<?php

declare(strict_types=1);

use App\Models\User;

it('lets an admin sign in and reach the teacher creation form in the browser', function (): void {
    User::factory()->superAdmin()->create([
        'username' => 'registrar',
        'password' => 'password',
        'full_name' => 'System Administrator',
    ]);

    $page = visit('/login');

    $page->assertSee('NutriMind')
        ->type('username', 'registrar')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/admin/dashboard')
        ->assertSee('Dashboard')
        ->click('Create Teacher')
        ->assertPathIs('/admin/teachers/create')
        ->assertSee('Create Teacher')
        ->assertSee('Full Name')
        ->assertSee('Username')
        ->assertSee('Grade')
        ->assertSee('Section')
        ->type('full_name', 'Browser Teacher')
        ->type('username', 'browserteacher')
        ->select('grade', '5')
        ->type('section', 'Section B')
        ->assertSee('Create Teacher');
});
