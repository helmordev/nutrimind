<?php

declare(strict_types=1);

use App\Models\User;

test('login page renders successfully', function (): void {
    $this->get('/login')
        ->assertOk()
        ->assertViewIs('auth.login')
        ->assertSee('NutriMind')
        ->assertSee('Sign in to your account', false);
});

test('teacher can login and is redirected to teacher class page', function (): void {
    $teacher = User::factory()->teacher()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $teacher->username,
        'password' => 'password123',
    ])
        ->assertRedirect(route('teacher.class'));

    $this->assertAuthenticatedAs($teacher);
});

test('super admin can login and is redirected to admin dashboard', function (): void {
    $admin = User::factory()->superAdmin()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $admin->username,
        'password' => 'password123',
    ])
        ->assertRedirect('/admin/dashboard');

    $this->assertAuthenticatedAs($admin);
});

test('login fails with invalid credentials', function (): void {
    $teacher = User::factory()->teacher()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $teacher->username,
        'password' => 'wrong-password',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['username' => 'The provided credentials are incorrect.']);

    $this->assertGuest();
});

test('login fails when account is inactive', function (): void {
    $teacher = User::factory()->teacher()->inactive()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $teacher->username,
        'password' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['username' => 'This account has been deactivated.']);

    $this->assertGuest();
});

test('student cannot login via web', function (): void {
    $student = User::factory()->student()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $student->username,
        'password' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['username' => 'Students must log in through the mobile app.']);

    $this->assertGuest();
});

test('authenticated user can logout', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

test('unauthenticated user cannot logout', function (): void {
    $this->post('/logout')
        ->assertRedirect('/login');
});

test('username is required', function (): void {
    $this->post('/login', [
        'password' => 'password123',
    ])
        ->assertSessionHasErrors(['username']);
});

test('password is required', function (): void {
    $this->post('/login', [
        'username' => 'teacher1',
    ])
        ->assertSessionHasErrors(['password']);
});

test('authenticated user is redirected away from login page', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get('/login')
        ->assertRedirect();
});

test('login preserves username input on failed attempt', function (): void {
    $this->post('/login', [
        'username' => 'nonexistent',
        'password' => 'wrong-password',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['username'])
        ->assertSessionHasInput('username', 'nonexistent');
});

test('login does not preserve password input on failed attempt', function (): void {
    $this->post('/login', [
        'username' => 'nonexistent',
        'password' => 'wrong-password',
    ]);

    $this->get('/login')
        ->assertDontSee('wrong-password');
});

test('session is regenerated after successful login', function (): void {
    $teacher = User::factory()->teacher()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $teacher->username,
        'password' => 'password123',
    ])->assertRedirect(route('teacher.class'));

    $this->assertAuthenticatedAs($teacher);
});

test('teacher with forced password change is redirected to password change form after login', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create([
        'password' => bcrypt('password123'),
    ]);

    $this->post('/login', [
        'username' => $teacher->username,
        'password' => 'password123',
    ])->assertRedirect(route('teacher.password.edit'));

    $this->assertAuthenticatedAs($teacher);
});

test('login with non-existent username fails', function (): void {
    $this->post('/login', [
        'username' => 'does_not_exist',
        'password' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['username' => 'The provided credentials are incorrect.']);

    $this->assertGuest();
});
