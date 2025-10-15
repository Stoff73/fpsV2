<?php

declare(strict_types=1);

use App\Models\User;

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token');

    $response = $this->withToken($token->plainTextToken)
        ->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);

    // Token should be deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

test('logout deletes only current token', function () {
    $user = User::factory()->create();

    // Create two tokens
    $token1 = $user->createToken('token1');
    $token2 = $user->createToken('token2');

    // Logout with token1
    $response = $this->withToken($token1->plainTextToken)
        ->postJson('/api/auth/logout');

    $response->assertStatus(200);

    // Token1 should be deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token1->accessToken->id,
    ]);

    // Token2 should still exist
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $token2->accessToken->id,
    ]);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
});

test('invalid token cannot logout', function () {
    $response = $this->withToken('invalid-token-123')
        ->postJson('/api/auth/logout');

    $response->assertStatus(401);
});
