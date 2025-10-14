<?php

declare(strict_types=1);

use App\Models\User;

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'access_token',
                'token_type',
            ],
        ]);

    expect($response->json('data.user.email'))->toBe('test@example.com');
    expect($response->json('data.token_type'))->toBe('Bearer');
});

test('user login creates new access token', function () {
    User::factory()->create([
        'email' => 'token@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'token@example.com',
        'password' => 'password123',
    ]);

    expect($response->json('data.access_token'))->not()->toBeNull();

    $this->assertDatabaseHas('personal_access_tokens', [
        'name' => 'auth_token',
    ]);
});

test('user cannot login with invalid email', function () {
    User::factory()->create([
        'email' => 'valid@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'wrong@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('user cannot login with invalid password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('correctpassword'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('login requires email and password', function () {
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('login requires valid email format', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('multiple successful logins create multiple tokens', function () {
    $user = User::factory()->create([
        'email' => 'multi@example.com',
        'password' => bcrypt('password123'),
    ]);

    // First login
    $response1 = $this->postJson('/api/auth/login', [
        'email' => 'multi@example.com',
        'password' => 'password123',
    ]);

    $token1 = $response1->json('data.access_token');

    // Second login
    $response2 = $this->postJson('/api/auth/login', [
        'email' => 'multi@example.com',
        'password' => 'password123',
    ]);

    $token2 = $response2->json('data.access_token');

    expect($token1)->not()->toBe($token2);

    // Both tokens should be in database
    $tokenCount = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->count();
    expect($tokenCount)->toBe(2);
});
