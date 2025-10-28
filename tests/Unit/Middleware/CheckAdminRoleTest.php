<?php

declare(strict_types=1);

use App\Http\Middleware\CheckAdminRole;
use App\Models\User;

uses(Tests\TestCase::class);

test('allows admin user to proceed', function () {
    $adminUser = User::factory()->create([
        'role' => 'admin',
    ]);

    $request = \Illuminate\Http\Request::create('/api/test', 'GET');
    $request->setUserResolver(fn () => $adminUser);

    $next = function ($request) {
        return response('Success', 200);
    };

    $middleware = new CheckAdminRole;
    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('Success');
});

test('blocks non-admin user with 403 forbidden', function () {
    $regularUser = User::factory()->create([
        'role' => 'user',
    ]);

    $request = \Illuminate\Http\Request::create('/api/test', 'GET');
    $request->setUserResolver(fn () => $regularUser);

    $next = function ($request) {
        return response('Success', 200);
    };

    $middleware = new CheckAdminRole;
    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(403);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('success', false);
    expect($data)->toHaveKey('message', 'Forbidden. Admin access required.');
});

test('blocks unauthenticated user with 401 unauthorized', function () {
    $request = \Illuminate\Http\Request::create('/api/test', 'GET');
    $request->setUserResolver(fn () => null);

    $next = function ($request) {
        return response('Success', 200);
    };

    $middleware = new CheckAdminRole;
    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(401);

    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveKey('success', false);
    expect($data)->toHaveKey('message', 'Unauthenticated.');
});
