<?php

declare(strict_types=1);

use App\Models\StudentProfile;
use App\Models\User;

test('student can login with valid LRN and PIN', function (): void {
    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123456',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'student' => ['id', 'full_name', 'grade', 'section', 'must_change_password'],
        ])
        ->assertJsonFragment([
            'message' => 'Login successful.',
            'id' => $student->id,
            'full_name' => $student->full_name,
        ]);
});

test('login updates last_login_at on student profile', function (): void {
    $student = User::factory()->student()->create();
    $profile = StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
        'last_login_at' => null,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123456',
    ])->assertOk();

    expect($profile->fresh()->last_login_at)->not->toBeNull();
});

test('login returns a valid sanctum token', function (): void {
    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123456',
    ])->assertOk();

    $token = $response->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonFragment(['id' => $student->id]);
});

test('login fails with incorrect PIN', function (): void {
    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '000000',
    ])
        ->assertUnauthorized()
        ->assertJsonFragment(['message' => 'The provided credentials are incorrect.']);
});

test('login fails with non-existent LRN', function (): void {
    $this->postJson('/api/v1/auth/login', [
        'lrn' => '999999999999',
        'pin' => '123456',
    ])
        ->assertUnauthorized()
        ->assertJsonFragment(['message' => 'The provided credentials are incorrect.']);
});

test('login fails when student account is inactive', function (): void {
    $student = User::factory()->student()->inactive()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123456',
    ])
        ->assertForbidden()
        ->assertJsonFragment(['message' => 'This account has been deactivated.']);
});

test('login validates LRN is required', function (): void {
    $this->postJson('/api/v1/auth/login', [
        'pin' => '123456',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lrn']);
});

test('login validates PIN is required', function (): void {
    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pin']);
});

test('login validates LRN must be exactly 12 characters', function (): void {
    $this->postJson('/api/v1/auth/login', [
        'lrn' => '12345',
        'pin' => '123456',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['lrn']);
});

test('login validates PIN must be exactly 6 characters', function (): void {
    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pin']);
});

test('authenticated student can logout', function (): void {
    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123456',
    ])->assertOk();

    $token = $response->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJsonFragment(['message' => 'Logged out successfully.']);

    expect($student->tokens()->count())->toBe(0);
});

test('unauthenticated logout returns 401', function (): void {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized();
});

test('login response includes must_change_password flag', function (): void {
    $student = User::factory()->student()->mustChangePassword()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => '123456',
    ])
        ->assertOk()
        ->assertJsonFragment(['must_change_password' => true]);
});
