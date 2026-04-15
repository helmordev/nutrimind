<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\StudentProfile;
use App\Models\User;

test('admin dashboard shows overview counts and navigation links', function (): void {
    $admin = User::factory()->superAdmin()->create();
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create([
        'teacher_id' => $teacher->id,
    ]);
    Classroom::factory()->for($teacher, 'teacher')->create([
        'is_active' => true,
    ]);
    Classroom::factory()->for($teacher, 'teacher')->inactive()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Admin Dashboard')
        ->assertSee('Total Teachers')
        ->assertSee('Total Students')
        ->assertSee('Active Classes')
        ->assertSee((string) User::query()->where('role', UserRole::Teacher)->count(), false)
        ->assertSee((string) User::query()->where('role', UserRole::Student)->count(), false)
        ->assertSee('1', false)
        ->assertSee(route('admin.teachers.create'), false)
        ->assertSee(route('admin.students.index'), false);
});

test('non admin is redirected away from admin dashboard', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('guest is redirected to login from admin dashboard', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('admin can view the read only student list', function (): void {
    $admin = User::factory()->superAdmin()->create();
    $teacher = User::factory()->teacher()->create([
        'full_name' => 'Teacher One',
    ]);
    $student = User::factory()->student()->create([
        'full_name' => 'Student One',
        'grade' => 5,
        'section' => 'A',
        'teacher_id' => $teacher->id,
    ]);
    StudentProfile::factory()->for($student, 'student')->create([
        'lrn' => '123456789012',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('Read-only Student List')
        ->assertSee('Student One')
        ->assertSee('123456789012')
        ->assertSee('Teacher One');
});
