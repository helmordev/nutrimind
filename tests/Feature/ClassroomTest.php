<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ──────────────────────────────────────────────
// Index
// ──────────────────────────────────────────────

test('teacher can view classrooms index', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.index'));

    $response->assertOk();
    $response->assertViewIs('teacher.classrooms.index');
});

test('teacher sees their own classrooms on index', function (): void {
    $teacher = User::factory()->teacher()->create();
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $otherTeacher = User::factory()->teacher()->create();
    Classroom::factory()->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.index'));

    $response->assertOk();
    $response->assertSeeText($classroom->name);
});

test('non-teacher is redirected from classrooms index', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->get(route('teacher.classrooms.index'));

    $response->assertRedirect(route('login'));
});

test('guest is redirected from classrooms index', function (): void {
    $response = $this->get(route('teacher.classrooms.index'));

    $response->assertRedirect(route('login'));
});

// ──────────────────────────────────────────────
// Create form
// ──────────────────────────────────────────────

test('teacher can view create classroom page', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.create'));

    $response->assertOk();
    $response->assertViewIs('teacher.classrooms.create');
});

test('non-teacher is redirected from create classroom page', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->get(route('teacher.classrooms.create'));

    $response->assertRedirect(route('login'));
});

test('guest is redirected from create classroom page', function (): void {
    $response = $this->get(route('teacher.classrooms.create'));

    $response->assertRedirect(route('login'));
});

// ──────────────────────────────────────────────
// Store
// ──────────────────────────────────────────────

test('teacher can create a classroom', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
        'name' => 'Grade 5 - Section A',
        'grade' => 5,
        'section' => 'A',
    ]);

    $this->assertDatabaseHas('classrooms', [
        'teacher_id' => $teacher->id,
        'name' => 'Grade 5 - Section A',
        'grade' => 5,
        'section' => 'A',
        'is_active' => true,
    ]);

    $classroom = Classroom::query()->where('teacher_id', $teacher->id)->first();
    $response->assertRedirect(route('teacher.classrooms.show', $classroom));
    $response->assertSessionHas('success');
});

test('classroom gets a unique 6-character room code', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
        'name' => 'Grade 6 - Section B',
        'grade' => 6,
        'section' => 'B',
    ]);

    $classroom = Classroom::query()->where('teacher_id', $teacher->id)->first();
    expect($classroom->room_code)->toHaveLength(6);
    expect($classroom->room_code)->toMatch('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{6}$/');
});

test('room codes are unique across classrooms', function (): void {
    $teacher = User::factory()->teacher()->create();

    Classroom::factory()->count(5)->create(['teacher_id' => $teacher->id]);

    $codes = Classroom::query()->pluck('room_code')->toArray();
    expect($codes)->toHaveCount(5);
    expect(array_unique($codes))->toHaveCount(5);
});

test('validation requires name', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('name');
});

test('validation requires grade', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
        'name' => 'Test Classroom',
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('grade');
});

test('validation requires valid grade (5 or 6)', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
        'name' => 'Bad Grade Classroom',
        'grade' => 7,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('grade');
});

test('validation requires section', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.classrooms.store'), [
        'name' => 'No Section Classroom',
        'grade' => 5,
    ]);

    $response->assertSessionHasErrors('section');
});

test('non-teacher cannot POST create classroom', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->post(route('teacher.classrooms.store'), [
        'name' => 'Unauthorized Classroom',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertRedirect(route('login'));
});

test('guest cannot POST create classroom', function (): void {
    $response = $this->post(route('teacher.classrooms.store'), [
        'name' => 'Guest Classroom',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertRedirect(route('login'));
});

// ──────────────────────────────────────────────
// Show
// ──────────────────────────────────────────────

test('teacher can view their own classroom', function (): void {
    $teacher = User::factory()->teacher()->create();
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.show', $classroom));

    $response->assertOk();
    $response->assertViewIs('teacher.classrooms.show');
    $response->assertSeeText($classroom->name);
    $response->assertSeeText($classroom->room_code);
});

test('teacher cannot view another teachers classroom', function (): void {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $classroom = Classroom::factory()->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.show', $classroom));

    $response->assertForbidden();
});

test('classroom show displays students', function (): void {
    $teacher = User::factory()->teacher()->create();
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);
    $student = User::factory()->student()->create([
        'teacher_id' => $teacher->id,
        'classroom_id' => $classroom->id,
    ]);

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.show', $classroom));

    $response->assertOk();
    $response->assertSeeText($student->full_name);
});

test('classroom show displays empty state when no students', function (): void {
    $teacher = User::factory()->teacher()->create();
    $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get(route('teacher.classrooms.show', $classroom));

    $response->assertOk();
    $response->assertSeeText('No students in this classroom yet');
});

test('non-teacher is redirected from classroom show', function (): void {
    $student = User::factory()->student()->create();
    $classroom = Classroom::factory()->create();

    $response = $this->actingAs($student)->get(route('teacher.classrooms.show', $classroom));

    $response->assertRedirect(route('login'));
});

test('guest is redirected from classroom show', function (): void {
    $classroom = Classroom::factory()->create();

    $response = $this->get(route('teacher.classrooms.show', $classroom));

    $response->assertRedirect(route('login'));
});

// ──────────────────────────────────────────────
// Model: generateRoomCode
// ──────────────────────────────────────────────

test('generateRoomCode produces 6-character uppercase alphanumeric code', function (): void {
    $code = Classroom::generateRoomCode();

    expect($code)->toHaveLength(6);
    expect($code)->toMatch('/^[ABCDEFGHJKLMNPQRSTUVWXYZ23456789]{6}$/');
});

test('generateRoomCode excludes ambiguous characters', function (): void {
    // Generate many codes and verify none contain ambiguous characters
    for ($i = 0; $i < 50; $i++) {
        $code = Classroom::generateRoomCode();
        expect($code)->not->toContain('I');
        expect($code)->not->toContain('O');
        expect($code)->not->toContain('1');
        expect($code)->not->toContain('0');
    }
});

// ──────────────────────────────────────────────
// Factory
// ──────────────────────────────────────────────

test('classroom factory creates valid classroom', function (): void {
    $classroom = Classroom::factory()->create();

    expect($classroom->id)->not->toBeNull();
    expect($classroom->teacher_id)->not->toBeNull();
    expect($classroom->name)->not->toBeEmpty();
    expect($classroom->room_code)->toHaveLength(6);
    expect($classroom->is_active)->toBeTrue();
    expect($classroom->grade)->toBeIn([5, 6]);
});

test('classroom factory inactive state works', function (): void {
    $classroom = Classroom::factory()->inactive()->create();

    expect($classroom->is_active)->toBeFalse();
});
