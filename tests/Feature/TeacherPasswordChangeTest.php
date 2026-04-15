<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('teacher can view forced password change page', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create();

    $response = $this->actingAs($teacher)->get(route('teacher.password.edit'));

    $response->assertOk();
    $response->assertViewIs('teacher.change-password');
    $response->assertSee('Change Your Password');
});

test('guest is redirected to login from forced password change page', function (): void {
    $this->get(route('teacher.password.edit'))
        ->assertRedirect(route('login'));
});

test('non teacher is redirected from forced password change page', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('teacher.password.edit'))
        ->assertRedirect(route('login'));
});

test('teacher can change password and clear forced password flag', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create([
        'password' => bcrypt('temporary-password'),
    ]);

    $response = $this->actingAs($teacher)->post(route('teacher.password.update'), [
        'current_password' => 'temporary-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('teacher.dashboard'));
    $response->assertSessionHas('success', 'Password changed successfully.');

    expect($teacher->fresh())
        ->must_change_password->toBeFalse()
        ->and(Hash::check('new-password', $teacher->fresh()->password))->toBeTrue();
});

test('teacher password change requires correct current password', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create([
        'password' => bcrypt('temporary-password'),
    ]);

    $this->actingAs($teacher)->post(route('teacher.password.update'), [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasErrors(['current_password' => 'The current password is incorrect.']);
});

test('teacher password change requires confirmed password', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create([
        'password' => bcrypt('temporary-password'),
    ]);

    $this->actingAs($teacher)->post(route('teacher.password.update'), [
        'current_password' => 'temporary-password',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ])->assertSessionHasErrors('password');
});

test('teacher password change requires minimum password length', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create([
        'password' => bcrypt('temporary-password'),
    ]);

    $this->actingAs($teacher)->post(route('teacher.password.update'), [
        'current_password' => 'temporary-password',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');
});

test('teacher with forced password change cannot access dashboard until password is changed', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create();

    $this->actingAs($teacher)
        ->get(route('teacher.dashboard'))
        ->assertRedirect(route('teacher.password.edit'));
});

test('teacher can access dashboard after changing password', function (): void {
    $teacher = User::factory()->teacher()->create([
        'password' => bcrypt('updated-password'),
        'must_change_password' => false,
    ]);

    $this->actingAs($teacher)
        ->get(route('teacher.dashboard'))
        ->assertOk();
});
