<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFamilyMemberRequest;
use App\Http\Requests\UpdateFamilyMemberRequest;
use App\Models\FamilyMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $familyMember = FamilyMember::create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            ...$request->validated(),
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
     * Display the specified family member.
     *
     * GET /api/user/family-members/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $familyMember = FamilyMember::where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'family_member' => $familyMember,
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

        $familyMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'Family member deleted successfully',
        ]);
    }
}
