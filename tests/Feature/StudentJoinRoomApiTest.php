<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\User;

test('authenticated student can join a classroom by room code', function (): void {
    $classroom = Classroom::factory()->forGrade(5)->create();
    $student = User::factory()->student()->create([
        'grade' => 5,
        'classroom_id' => null,
    ]);

    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => $classroom->room_code,
        ])
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'classroom' => [
                'id',
                'name',
                'grade',
                'section',
                'room_code',
            ],
        ])
        ->assertJsonPath('classroom.id', $classroom->id)
        ->assertJsonPath('classroom.name', $classroom->name);

    expect($student->fresh()?->classroom_id)->toBe($classroom->id);
});

test('student cannot join a room with an invalid room code', function (): void {
    $student = User::factory()->student()->create(['classroom_id' => null]);
    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => 'XXXXXX',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['room_code']);
});

test('student cannot join an inactive classroom', function (): void {
    $classroom = Classroom::factory()->inactive()->create();
    $student = User::factory()->student()->create(['classroom_id' => null]);
    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => $classroom->room_code,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['room_code']);
});

test('student who is already in a classroom can switch to a new one', function (): void {
    $oldClassroom = Classroom::factory()->forGrade(5)->create();
    $newClassroom = Classroom::factory()->forGrade(5)->create();
    $student = User::factory()->student()->create([
        'grade' => 5,
        'classroom_id' => $oldClassroom->id,
    ]);

    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => $newClassroom->room_code,
        ])
        ->assertOk()
        ->assertJsonPath('classroom.id', $newClassroom->id);

    expect($student->fresh()?->classroom_id)->toBe($newClassroom->id);
});

test('room code validation requires the field', function (): void {
    $student = User::factory()->student()->create();
    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['room_code']);
});

test('room code must be exactly 6 characters', function (): void {
    $student = User::factory()->student()->create();
    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => 'ABC',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['room_code']);
});

test('join room endpoint requires authentication', function (): void {
    $this->postJson('/api/v1/student/join-room', [
        'room_code' => 'ABCDEF',
    ])->assertUnauthorized();
});

test('non student cannot join a room', function (): void {
    $teacher = User::factory()->teacher()->create();
    $token = $teacher->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => 'ABCDEF',
        ])
        ->assertForbidden();
});

test('room code is case insensitive', function (): void {
    $classroom = Classroom::factory()->forGrade(5)->create([
        'room_code' => 'ABC234',
    ]);
    $student = User::factory()->student()->create([
        'grade' => 5,
        'classroom_id' => null,
    ]);

    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => 'abc234',
        ])
        ->assertOk()
        ->assertJsonPath('classroom.id', $classroom->id);
});

test('student cannot rejoin the same classroom they are already in', function (): void {
    $classroom = Classroom::factory()->forGrade(5)->create();
    $student = User::factory()->student()->create([
        'grade' => 5,
        'classroom_id' => $classroom->id,
    ]);

    $token = $student->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/student/join-room', [
            'room_code' => $classroom->room_code,
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'You are already in this classroom.');
});
