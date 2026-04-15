<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('admin can view the create teacher page', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get('/admin/teachers/create')
        ->assertOk()
        ->assertSee('Create Teacher');
});

test('non-admin cannot view the create teacher page', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get('/admin/teachers/create')
        ->assertRedirect(route('login'));
});

test('guest cannot view the create teacher page', function (): void {
    $this->get('/admin/teachers/create')
        ->assertRedirect('/login');
});

test('admin can create a teacher account', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Maria Santos',
            'username' => 'msantos',
            'grade' => 5,
            'section' => 'Section A',
        ])
        ->assertRedirect(route('admin.teachers.create'))
        ->assertSessionHas('success')
        ->assertSessionHas('temporary_password')
        ->assertSessionHas('teacher_name', 'Maria Santos')
        ->assertSessionHas('teacher_username', 'msantos');

    $this->assertDatabaseHas('users', [
        'full_name' => 'Maria Santos',
        'username' => 'msantos',
        'role' => UserRole::Teacher->value,
        'grade' => 5,
        'section' => 'Section A',
        'is_active' => true,
        'must_change_password' => true,
    ]);
});

test('created teacher has a hashed password', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Test Teacher',
            'username' => 'testteacher',
            'grade' => 6,
            'section' => 'Section B',
        ]);

    $teacher = User::query()->where('username', 'testteacher')->first();

    expect($teacher)->not->toBeNull()
        ->and($teacher->password)->not->toBe('')
        ->and(password_verify('anything', (string) $teacher->password))->toBeFalse();
});

test('temporary password is 8 characters long', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Temp Pass Teacher',
            'username' => 'temppass',
            'grade' => 5,
            'section' => 'Section C',
        ]);

    $tempPassword = session('temporary_password');
    expect($tempPassword)->toBeString()->toHaveLength(8);
});

test('create teacher requires full_name', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'username' => 'msantos',
            'grade' => 5,
            'section' => 'Section A',
        ])
        ->assertSessionHasErrors('full_name');
});

test('create teacher requires username', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Maria Santos',
            'grade' => 5,
            'section' => 'Section A',
        ])
        ->assertSessionHasErrors('username');
});

test('create teacher requires unique username', function (): void {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->teacher()->create(['username' => 'existing']);

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Maria Santos',
            'username' => 'existing',
            'grade' => 5,
            'section' => 'Section A',
        ])
        ->assertSessionHasErrors('username');
});

test('create teacher requires valid grade', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Maria Santos',
            'username' => 'msantos',
            'grade' => 7,
            'section' => 'Section A',
        ])
        ->assertSessionHasErrors('grade');
});

test('create teacher requires section', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/admin/teachers', [
            'full_name' => 'Maria Santos',
            'username' => 'msantos',
            'grade' => 5,
        ])
        ->assertSessionHasErrors('section');
});

test('non-admin cannot create a teacher account', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->post('/admin/teachers', [
            'full_name' => 'Maria Santos',
            'username' => 'msantos',
            'grade' => 5,
            'section' => 'Section A',
        ])
        ->assertRedirect(route('login'));

    $this->assertDatabaseMissing('users', ['username' => 'msantos']);
});

test('guest cannot create a teacher account', function (): void {
    $this->post('/admin/teachers', [
        'full_name' => 'Maria Santos',
        'username' => 'msantos',
        'grade' => 5,
        'section' => 'Section A',
    ])->assertRedirect('/login');

    $this->assertDatabaseMissing('users', ['username' => 'msantos']);
});

test('admin dashboard page renders successfully', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Dashboard');
});
