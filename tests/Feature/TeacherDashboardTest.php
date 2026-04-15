<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('teacher can view class page with their student list and actions', function (): void {
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create([
        'teacher_id' => $teacher->id,
        'grade' => 5,
        'section' => 'A',
        'full_name' => 'Maria Santos',
    ]);
    $student->studentProfile()->create([
        'lrn' => '123456789012',
        'pin' => '123456',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.class'));

    $response->assertOk();
    $response->assertViewIs('teacher.class');
    $response->assertSeeText('My Class');
    $response->assertSeeText('Maria Santos');
    $response->assertSeeText('123456789012');
    $response->assertSeeText('Grade 5');
    $response->assertSeeText('A');
    $response->assertSee(route('teacher.students.create'));
    $response->assertSee(route('teacher.password.edit'));
});

test('teacher class page only shows their own students', function (): void {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();

    $ownedStudent = User::factory()->student()->create([
        'teacher_id' => $teacher->id,
        'full_name' => 'Owned Student',
    ]);
    $ownedStudent->studentProfile()->create([
        'lrn' => '111111111111',
        'pin' => '123456',
    ]);

    $otherStudent = User::factory()->student()->create([
        'teacher_id' => $otherTeacher->id,
        'full_name' => 'Other Student',
    ]);
    $otherStudent->studentProfile()->create([
        'lrn' => '222222222222',
        'pin' => '123456',
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.class'));

    $response->assertOk();
    $response->assertSeeText('Owned Student');
    $response->assertDontSeeText('Other Student');
});

test('teacher dashboard route renders the class page for compatibility', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

    $response->assertOk();
    $response->assertViewIs('teacher.class');
});

test('teacher students index redirects to class page', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)
        ->get(route('teacher.students.index'))
        ->assertRedirect(route('teacher.class'));
});

test('guest and non teacher are redirected from class page', function (): void {
    $teacher = User::factory()->teacher()->create();
    Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $student = User::factory()->student()->create();

    $this->get(route('teacher.class'))
        ->assertRedirect(route('login'));

    $this->actingAs($student)
        ->get(route('teacher.class'))
        ->assertRedirect(route('login'));
});
