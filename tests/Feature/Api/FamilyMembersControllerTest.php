<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Household;
use App\Models\FamilyMember;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a household
    $this->household = Household::factory()->create();

    // Create a test user
    $this->user = User::factory()->create([
        'household_id' => $this->household->id,
    ]);

    // Create some family members
    $this->child1 = FamilyMember::factory()->create([
        'user_id' => $this->user->id,
        'relationship' => 'child',
        'name' => 'Child One',
        'date_of_birth' => '2015-03-10',
        'is_dependent' => true,
    ]);

    $this->child2 = FamilyMember::factory()->create([
        'user_id' => $this->user->id,
        'relationship' => 'child',
        'name' => 'Child Two',
        'date_of_birth' => '2018-07-22',
        'is_dependent' => true,
    ]);

    // Authenticate as this user
    $this->actingAs($this->user, 'sanctum');
});

describe('GET /api/user/family-members', function () {
    test('returns all family members for authenticated user', function () {
        $response = $this->getJson('/api/user/family-members');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'family_members',
                    'count',
                ],
            ]);

        expect($response->json('data.family_members'))->toHaveCount(2);
        expect($response->json('data.count'))->toBe(2);
        expect($response->json('data.family_members.0.name'))->toBe('Child One');
        expect($response->json('data.family_members.1.name'))->toBe('Child Two');
    });

    test('returns empty array when user has no family members', function () {
        // Delete existing family members
        FamilyMember::where('user_id', $this->user->id)->delete();

        $response = $this->getJson('/api/user/family-members');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'family_members' => [],
                    'count' => 0,
                ],
            ]);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson('/api/user/family-members');

        $response->assertStatus(401);
    });
});

describe('GET /api/user/family-members/{id}', function () {
    test('returns a specific family member', function () {
        $response = $this->getJson("/api/user/family-members/{$this->child1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'family_member' => [
                        'id' => $this->child1->id,
                        'name' => 'Child One',
                        'relationship' => 'child',
                    ],
                ],
            ]);
    });

    test('returns 404 for non-existent family member', function () {
        $response = $this->getJson('/api/user/family-members/99999');

        $response->assertStatus(404);
    });

    test('user cannot view another user family member', function () {
        // Create another user with family member
        $otherUser = User::factory()->create([
            'household_id' => Household::factory()->create()->id,
        ]);
        $otherFamilyMember = FamilyMember::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other User Child',
        ]);

        $response = $this->getJson("/api/user/family-members/{$otherFamilyMember->id}");

        $response->assertStatus(404);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson("/api/user/family-members/{$this->child1->id}");

        $response->assertStatus(401);
    });
});

describe('POST /api/user/family-members', function () {
    test('creates a new family member successfully', function () {
        $newMemberData = [
            'relationship' => 'child',
            'name' => 'New Child',
            'date_of_birth' => '2020-05-15',
            'gender' => 'female',
            'is_dependent' => true,
            'education_status' => 'primary',
            'annual_income' => 0,
            'notes' => 'Third child',
        ];

        $response = $this->postJson('/api/user/family-members', $newMemberData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Family member added successfully',
            ]);

        // Verify database
        $this->assertDatabaseHas('family_members', [
            'user_id' => $this->user->id,
            'name' => 'New Child',
            'relationship' => 'child',
        ]);

        // Verify response data
        expect($response->json('data.family_member.name'))->toBe('New Child');
        expect($response->json('data.family_member.relationship'))->toBe('child');
    });

    test('validates required fields', function () {
        $invalidData = [
            'relationship' => '', // Required
            'name' => '', // Required
        ];

        $response = $this->postJson('/api/user/family-members', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['relationship', 'name']);
    });

    test('validates relationship enum values', function () {
        $invalidData = [
            'relationship' => 'invalid_relationship',
            'name' => 'Test Name',
        ];

        $response = $this->postJson('/api/user/family-members', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['relationship']);
    });

    test('validates date format', function () {
        $invalidData = [
            'relationship' => 'child',
            'name' => 'Test Child',
            'date_of_birth' => 'not-a-date',
        ];

        $response = $this->postJson('/api/user/family-members', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_of_birth']);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson('/api/user/family-members', [
            'relationship' => 'child',
            'name' => 'Test',
        ]);

        $response->assertStatus(401);
    });
});

describe('PUT /api/user/family-members/{id}', function () {
    test('updates a family member successfully', function () {
        $updatedData = [
            'name' => 'Updated Child Name',
            'gender' => 'male',
            'is_dependent' => false,
            'annual_income' => 5000.00,
            'notes' => 'Now earning part-time income',
        ];

        $response = $this->putJson("/api/user/family-members/{$this->child1->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Family member updated successfully',
            ]);

        // Verify database
        $this->assertDatabaseHas('family_members', [
            'id' => $this->child1->id,
            'name' => 'Updated Child Name',
            'is_dependent' => false,
        ]);
    });

    test('user cannot update another user family member', function () {
        // Create another user with family member
        $otherUser = User::factory()->create([
            'household_id' => Household::factory()->create()->id,
        ]);
        $otherFamilyMember = FamilyMember::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/user/family-members/{$otherFamilyMember->id}", [
            'name' => 'Unauthorized Update',
        ]);

        $response->assertStatus(404);

        // Verify database was not updated
        $this->assertDatabaseHas('family_members', [
            'id' => $otherFamilyMember->id,
            'name' => 'Original Name',
        ]);
    });

    test('returns 404 for non-existent family member', function () {
        $response = $this->putJson('/api/user/family-members/99999', [
            'name' => 'Test',
        ]);

        $response->assertStatus(404);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->putJson("/api/user/family-members/{$this->child1->id}", [
            'name' => 'Test',
        ]);

        $response->assertStatus(401);
    });
});

describe('DELETE /api/user/family-members/{id}', function () {
    test('deletes a family member successfully', function () {
        $response = $this->deleteJson("/api/user/family-members/{$this->child1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Family member deleted successfully',
            ]);

        // Verify database
        $this->assertDatabaseMissing('family_members', [
            'id' => $this->child1->id,
        ]);

        // Verify soft delete if applicable, or hard delete
        expect(FamilyMember::find($this->child1->id))->toBeNull();
    });

    test('user cannot delete another user family member', function () {
        // Create another user with family member
        $otherUser = User::factory()->create([
            'household_id' => Household::factory()->create()->id,
        ]);
        $otherFamilyMember = FamilyMember::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other User Child',
        ]);

        $response = $this->deleteJson("/api/user/family-members/{$otherFamilyMember->id}");

        $response->assertStatus(404);

        // Verify database still has the record
        $this->assertDatabaseHas('family_members', [
            'id' => $otherFamilyMember->id,
        ]);
    });

    test('returns 404 for non-existent family member', function () {
        $response = $this->deleteJson('/api/user/family-members/99999');

        $response->assertStatus(404);
    });

    test('requires authentication', function () {
        // Create a new test instance without authentication
        $this->app = $this->createApplication();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->deleteJson("/api/user/family-members/{$this->child1->id}");

        $response->assertStatus(401);
    });
});
