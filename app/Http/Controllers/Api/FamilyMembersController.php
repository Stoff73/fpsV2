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

        \Log::info('FamilyMembers::index called', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'spouse_id' => $user->spouse_id,
        ]);

        $familyMembers = FamilyMember::where('user_id', $user->id)
            ->orderBy('relationship')
            ->orderBy('date_of_birth')
            ->get();

        // If user has a linked spouse, get spouse's children (NOT the spouse record itself)
        $spouseFamilyMembers = collect();
        if ($user->spouse_id) {
            $spouseFamilyMembers = FamilyMember::where('user_id', $user->spouse_id)
                ->where('relationship', 'child')  // Only children, not spouse record
                ->orderBy('date_of_birth')
                ->get();
        }

        // Add spouse email if applicable and mark spouse's children as shared
        $familyMembers = $familyMembers->map(function ($member) use ($user) {
            $memberArray = $member->toArray();
            $memberArray['is_shared'] = false;
            $memberArray['owner'] = 'self';

            // If this is a spouse and user has a spouse_id, get the spouse's email
            if ($member->relationship === 'spouse' && $user->spouse_id) {
                $spouse = \App\Models\User::find($user->spouse_id);
                $memberArray['email'] = $spouse ? $spouse->email : null;
            }

            return $memberArray;
        });

        // If user has a spouse but no spouse family_member record, add spouse from User record
        $hasOwnSpouseRecord = $familyMembers->contains(function ($fm) {
            return $fm['relationship'] === 'spouse';
        });

        if ($user->spouse_id && ! $hasOwnSpouseRecord) {
            $spouseUser = \App\Models\User::find($user->spouse_id);
            if ($spouseUser) {
                // Create a virtual spouse family member from the User record
                $familyMembers->push([
                    'id' => null,  // Virtual record, no ID
                    'user_id' => $user->id,
                    'household_id' => $user->household_id,
                    'relationship' => 'spouse',
                    'name' => $spouseUser->name,
                    'date_of_birth' => $spouseUser->date_of_birth?->format('Y-m-d'),
                    'gender' => $spouseUser->gender,
                    'national_insurance_number' => $spouseUser->national_insurance_number,
                    'annual_income' => $spouseUser->annual_employment_income,
                    'is_dependent' => false,
                    'notes' => null,
                    'email' => $spouseUser->email,
                    'is_shared' => false,
                    'owner' => 'self',
                    'created_at' => null,
                    'updated_at' => null,
                ]);
            }
        }

        // Process spouse's children (mark as shared if not duplicate)
        $sharedFromSpouse = $spouseFamilyMembers->map(function ($member) use ($familyMembers) {
            $memberArray = $member->toArray();

            // Check if this child already exists in user's family members (duplicate)
            $isDuplicate = $familyMembers->contains(function ($fm) use ($member) {
                return $fm['relationship'] === 'child' &&
                       $fm['name'] === $member->name &&
                       $fm['date_of_birth'] === $member->date_of_birth;
            });

            if (! $isDuplicate) {
                $memberArray['is_shared'] = true;
                $memberArray['owner'] = 'spouse';

                return $memberArray;
            }

            return null;
        })->filter(); // Remove nulls

        // Merge user's family members with spouse's shared records
        $allMembers = $familyMembers->concat($sharedFromSpouse);

        \Log::info('FamilyMembers::index result', [
            'own_members_count' => $familyMembers->count(),
            'spouse_members_count' => $spouseFamilyMembers->count(),
            'shared_from_spouse_count' => $sharedFromSpouse->count(),
            'total_members' => $allMembers->count(),
            'members' => $allMembers->map(function ($m) {
                return ['name' => $m['name'], 'relationship' => $m['relationship'], 'is_shared' => $m['is_shared']];
            })->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'family_members' => $allMembers->values(),
                'count' => $allMembers->count(),
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

        // Check for duplicate children when user has linked spouse
        if ($data['relationship'] === 'child' && $user->spouse_id) {
            $duplicateInUserRecords = FamilyMember::where('user_id', $user->id)
                ->where('relationship', 'child')
                ->where('name', $data['name'])
                ->where('date_of_birth', $data['date_of_birth'])
                ->exists();

            $duplicateInSpouseRecords = FamilyMember::where('user_id', $user->spouse_id)
                ->where('relationship', 'child')
                ->where('name', $data['name'])
                ->where('date_of_birth', $data['date_of_birth'])
                ->exists();

            if ($duplicateInUserRecords || $duplicateInSpouseRecords) {
                return response()->json([
                    'success' => false,
                    'message' => 'This child already exists in your or your spouse\'s family members. Children are automatically shared between linked spouses.',
                ], 422);
            }
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
            // Update spouse user's income if provided in family member data
            if (isset($data['annual_income']) && $data['annual_income'] > 0) {
                $spouseUser->annual_employment_income = $data['annual_income'];
            }
            $spouseUser->save();

            // Clear cached protection analysis for both users since spouse linkage affects completeness
            \Illuminate\Support\Facades\Cache::forget("protection_analysis_{$currentUser->id}");
            \Illuminate\Support\Facades\Cache::forget("protection_analysis_{$spouseUser->id}");

            // Create bidirectional spouse data sharing permissions
            \App\Models\SpousePermission::updateOrCreate(
                [
                    'user_id' => $currentUser->id,
                    'spouse_id' => $spouseUser->id,
                ],
                [
                    'can_view_data' => true,
                    'can_edit_data' => false,
                    'permission_granted_at' => now(),
                ]
            );

            \App\Models\SpousePermission::updateOrCreate(
                [
                    'user_id' => $spouseUser->id,
                    'spouse_id' => $currentUser->id,
                ],
                [
                    'can_view_data' => true,
                    'can_edit_data' => false,
                    'permission_granted_at' => now(),
                ]
            );

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
                \Log::error('Failed to send spouse account linked email: '.$e->getMessage());
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
            // Populate income fields - treat family member annual_income as employment income
            'annual_employment_income' => $data['annual_income'] ?? 0,
        ]);

        // Update current user
        $currentUser->spouse_id = $spouseUser->id;
        $currentUser->marital_status = 'married';
        $currentUser->save();

        // Clear cached protection analysis for current user since spouse linkage affects completeness
        \Illuminate\Support\Facades\Cache::forget("protection_analysis_{$currentUser->id}");

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
            \Log::error('Failed to send spouse account created email: '.$e->getMessage());
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
                'temporary_password' => $temporaryPassword, // Show to user so they can share with spouse
                'spouse_email' => $spouseEmail,
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

        $data = $request->validated();
        $familyMember->update($data);

        // If updating a spouse, sync the income to the spouse user account
        if ($familyMember->relationship === 'spouse' && $user->spouse_id) {
            $spouseUser = \App\Models\User::find($user->spouse_id);
            if ($spouseUser && isset($data['annual_income'])) {
                $spouseUser->annual_employment_income = $data['annual_income'];
                $spouseUser->save();

                // Clear protection analysis cache for both users
                \Illuminate\Support\Facades\Cache::forget("protection_analysis_{$user->id}");
                \Illuminate\Support\Facades\Cache::forget("protection_analysis_{$spouseUser->id}");
            }
        }

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
