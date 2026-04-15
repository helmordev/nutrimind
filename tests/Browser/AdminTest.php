<?php

declare(strict_types=1);

use App\Models\User;

it('shows the admin dashboard with correct stats', function (): void {
    User::factory()->superAdmin()->create([
        'username' => 'admin_dash',
        'password' => 'password',
    ]);

    // Create some teachers and students so the counts are non-zero.
    $teacher = User::factory()->teacher()->create();
    User::factory()->count(3)->create(['teacher_id' => $teacher->id]);

    $page = visit('/login');

    $page->type('username', 'admin_dash')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/admin/dashboard')
        ->assertSee('Dashboard')
        ->assertSee('Welcome back')
        ->assertSee('Teachers')
        ->assertSee('Students')
        ->assertSee('Active Classrooms');
});

it('lets an admin navigate to the students list page', function (): void {
    User::factory()->superAdmin()->create([
        'username' => 'admin_students',
        'password' => 'password',
    ]);

    $teacher = User::factory()->teacher()->create(['full_name' => 'Test Teacher']);
    User::factory()->create([
        'full_name' => 'Visible Student',
        'teacher_id' => $teacher->id,
    ]);

    $page = visit('/login');

    $page->type('username', 'admin_students')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/admin/dashboard')
        ->click('Students')
        ->assertPathIs('/admin/students')
        ->assertSee('All Students')
        ->assertSee('Visible Student')
        ->assertSee('Test Teacher');
});

it('shows the empty students state for admin', function (): void {
    User::factory()->superAdmin()->create([
        'username' => 'admin_empty',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'admin_empty')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/admin/dashboard')
        ->click('Students')
        ->assertPathIs('/admin/students')
        ->assertSee('No students yet');
});

it('lets an admin create a teacher and see success message', function (): void {
    User::factory()->superAdmin()->create([
        'username' => 'admin_create_t',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'admin_create_t')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/admin/dashboard')
        ->click('Create Teacher')
        ->assertPathIs('/admin/teachers/create')
        ->assertSee('Create Teacher')
        ->type('full_name', 'New Teacher Name')
        ->type('username', 'newteacher')
        ->select('grade', '5')
        ->type('section', 'Orchid')
        ->click('button.bg-brand')
        ->assertPathIs('/admin/teachers/create')
        ->assertSee('Teacher account created successfully');
});
