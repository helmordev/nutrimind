<?php

declare(strict_types=1);

use App\Models\User;

test('unauthenticated request to protected route returns 401', function (): void {
    $this->getJson('/api/v1/user')
        ->assertUnauthorized();
});

test('authenticated user can access protected route', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk();
});

test('user can create a personal access token', function (): void {
    $user = User::factory()->create();

    $token = $user->createToken('test-device');

    expect($token->plainTextToken)->toBeString()->not->toBeEmpty();
    expect($user->tokens)->toHaveCount(1);
    expect($user->tokens->first()->name)->toBe('test-device');
});

test('user can create token with specific abilities', function (): void {
    $user = User::factory()->create();

    $token = $user->createToken('test-device', ['game:play', 'profile:read']);

    $accessToken = $user->tokens->first();
    expect($accessToken->abilities)->toBe(['game:play', 'profile:read']);
});

test('token can authenticate API requests', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');

    $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonFragment(['id' => $user->id]);
});

test('revoked token cannot authenticate', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-device');
    $plainToken = $token->plainTextToken;

    // Revoke all tokens
    $user->tokens()->delete();

    $this->withHeader('Authorization', 'Bearer '.$plainToken)
        ->getJson('/api/v1/user')
        ->assertUnauthorized();
});

test('user can have multiple tokens', function (): void {
    $user = User::factory()->create();

    $user->createToken('phone');
    $user->createToken('tablet');
    $user->createToken('browser');

    expect($user->tokens()->count())->toBe(3);
});

test('revoking specific token does not affect others', function (): void {
    $user = User::factory()->create();

    $phoneToken = $user->createToken('phone');
    $tabletToken = $user->createToken('tablet');

    // Revoke only the phone token
    $user->tokens()->where('id', $phoneToken->accessToken->id)->delete();

    // Phone token should fail
    $this->withHeader('Authorization', 'Bearer '.$phoneToken->plainTextToken)
        ->getJson('/api/v1/user')
        ->assertUnauthorized();

    // Tablet token should still work
    $this->withHeader('Authorization', 'Bearer '.$tabletToken->plainTextToken)
        ->getJson('/api/v1/user')
        ->assertOk();
});

test('invalid token returns 401', function (): void {
    $this->withHeader('Authorization', 'Bearer invalid-token-string')
        ->getJson('/api/v1/user')
        ->assertUnauthorized();
});

test('teacher can authenticate via token', function (): void {
    $teacher = User::factory()->teacher()->create();
    $token = $teacher->createToken('teacher-device')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk();
});

test('super admin can authenticate via token', function (): void {
    $admin = User::factory()->superAdmin()->create();
    $token = $admin->createToken('admin-device')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk();
});

test('inactive user can still authenticate via token', function (): void {
    $user = User::factory()->inactive()->create();
    $token = $user->createToken('inactive-device')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk();
});

test('sanctum token expiration is configured', function (): void {
    $expiration = config('sanctum.expiration');

    expect($expiration)->not->toBeNull();
    expect((int) $expiration)->toBe(60 * 24 * 7); // 7 days
});

test('user endpoint returns correct user data', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('profile-device')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonFragment([
            'id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->full_name,
        ]);
});
