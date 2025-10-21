<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;
use App\Mail\SpouseAccountCreated;
use App\Mail\SpouseAccountLinked;
use App\Models\FamilyMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FamilyMembersController extends Controller
{
    /**
     * Display a listing of the authenticated user's family members.
     *
     * GET /api/user/family-members
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $familyMembers = FamilyMember::where('user_id', $user->id)
            ->orderBy('relationship')
            ->orderBy('date_of_birth')
            ->get();

        // Add spouse email if applicable
        $familyMembers = $familyMembers->map(function ($member) use ($user) {
            $memberArray = $member->toArray();

            // If this is a spouse and user has a spouse_id, get the spouse's email
            if ($member->relationship === 'spouse' && $user->spouse_id) {
                $spouse = \App\Models\User::find($user->spouse_id);
                $memberArray['email'] = $spouse ? $spouse->email : null;
            }

            return $memberArray;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'family_members' => $familyMembers,
                'count' => $familyMembers->count(),
            ],
        ]);
    }

    /**
     * Store a newly created family member.
     *
     * POST /api/user/family-members
     */
    public function store(StoreFamilyMemberRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Special handling for spouse relationship
        if ($data['relationship'] === 'spouse' && isset($data['email'])) {
            return $this->handleSpouseCreation($user, $data);
        }

        $familyMember = FamilyMember::create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            ...$data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Family member added successfully',
            'data' => [
                'family_member' => $familyMember,
            ],
        ], 201);
    }

    /**
     * Handle spouse creation or linking
     */
    private function handleSpouseCreation($currentUser, array $data): JsonResponse
    {
        $spouseEmail = $data['email'];

        // Check if spouse already has an account
        $spouseUser = \App\Models\User::where('email', $spouseEmail)->first();

        if ($spouseUser) {
            // Spouse already exists - link the accounts
            if ($spouseUser->id === $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot add yourself as a spouse',
                ], 422);
            }

            if ($spouseUser->spouse_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This user is already linked to another spouse',
                ], 422);
            }

            // Link both users
            $currentUser->spouse_id = $spouseUser->id;
            $currentUser->marital_status = 'married';
            $currentUser->save();

            $spouseUser->spouse_id = $currentUser->id;
            $spouseUser->marital_status = 'married';
            $spouseUser->save();

            // Create family member record
            $familyMember = FamilyMember::create([
                'user_id' => $currentUser->id,
                'household_id' => $currentUser->household_id,
                'relationship' => 'spouse',
                'name' => $data['name'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'national_insurance_number' => $data['national_insurance_number'] ?? null,
                'annual_income' => $data['annual_income'] ?? null,
                'is_dependent' => $data['is_dependent'] ?? false,
                'notes' => $data['notes'] ?? null,
            ]);

            // Send email notification to spouse
            try {
                Mail::to($spouseUser->email)->send(new SpouseAccountLinked($spouseUser, $currentUser));
            } catch (\Exception $e) {
                \Log::error('Failed to send spouse account linked email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Spouse account linked successfully',
                'data' => [
                    'family_member' => $familyMember,
                    'spouse_user' => $spouseUser,
                    'linked' => true,
                ],
            ], 201);
        }

        // Spouse doesn't exist - create new user account
        $temporaryPassword = \Illuminate\Support\Str::random(16);

        $spouseUser = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $spouseEmail,
            'password' => \Illuminate\Support\Facades\Hash::make($temporaryPassword),
            'must_change_password' => true,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'marital_status' => 'married',
            'spouse_id' => $currentUser->id,
            'household_id' => $currentUser->household_id,
            'is_primary_account' => false,
            'national_insurance_number' => $data['national_insurance_number'] ?? null,
        ]);

        // Update current user
        $currentUser->spouse_id = $spouseUser->id;
        $currentUser->marital_status = 'married';
        $currentUser->save();

        // Create family member record
        $familyMember = FamilyMember::create([
            'user_id' => $currentUser->id,
            'household_id' => $currentUser->household_id,
            'relationship' => 'spouse',
            'name' => $data['name'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'national_insurance_number' => $data['national_insurance_number'] ?? null,
            'annual_income' => $data['annual_income'] ?? null,
            'is_dependent' => $data['is_dependent'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);

        // Send email to spouse with temporary password
        try {
            Mail::to($spouseEmail)->send(new SpouseAccountCreated($spouseUser, $currentUser, $temporaryPassword));
        } catch (\Exception $e) {
            \Log::error('Failed to send spouse account created email: ' . $e->getMessage());
            // Also log the temporary password so it can be retrieved if email fails
            \Log::info("Temporary password for {$spouseEmail}: {$temporaryPassword}");
        }

        return response()->json([
            'success' => true,
            'message' => 'Spouse account created successfully. They will receive an email to set their password.',
            'data' => [
                'family_member' => $familyMember,
                'spouse_user' => $spouseUser,
                'created' => true,
            ],
        ], 201);
    }

    /**
     * Display the specified family member.
     *
     * GET /api/user/family-members/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $familyMember = FamilyMember::where('user_id', $user->id)
            ->findOrFail($id);

        $memberArray = $familyMember->toArray();

        // If this is a spouse and user has a spouse_id, get the spouse's email
        if ($familyMember->relationship === 'spouse' && $user->spouse_id) {
            $spouse = \App\Models\User::find($user->spouse_id);
            $memberArray['email'] = $spouse ? $spouse->email : null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'family_member' => $memberArray,
            ],
        ]);
    }

    /**
     * Update the specified family member.
     *
     * PUT /api/user/family-members/{id}
     */
    public function update(UpdateFamilyMemberRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $familyMember = FamilyMember::where('user_id', $user->id)
            ->findOrFail($id);

        $familyMember->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Family member updated successfully',
            'data' => [
                'family_member' => $familyMember->fresh(),
            ],
        ]);
    }

    /**
     * Remove the specified family member.
     *
     * DELETE /api/user/family-members/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $familyMember = FamilyMember::where('user_id', $user->id)
            ->findOrFail($id);

        // If deleting a spouse, clear the spouse linkage
        if ($familyMember->relationship === 'spouse' && $user->spouse_id) {
            $spouseUser = \App\Models\User::find($user->spouse_id);

            if ($spouseUser) {
                // Clear spouse linkage for both users
                $spouseUser->spouse_id = null;
                $spouseUser->save();
            }

            $user->spouse_id = null;
            $user->save();
        }

        $familyMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'Family member deleted successfully',
        ]);
    }
}
