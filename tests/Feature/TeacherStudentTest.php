<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('teacher can view create student page', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get(route('teacher.students.create'));

    $response->assertOk();
    $response->assertViewIs('teacher.students.create');
});

test('non-teacher is redirected from create student page', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->get(route('teacher.students.create'));

    $response->assertRedirect(route('login'));
});

test('guest is redirected to login from create student page', function (): void {
    $response = $this->get(route('teacher.students.create'));

    $response->assertRedirect(route('login'));
});

test('teacher can create a student account', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Maria Santos',
        'username' => 'msantos',
        'lrn' => '123456789012',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertRedirect(route('teacher.students.create'));
    $response->assertSessionHas('success');
    $response->assertSessionHas('temporary_password');
    $response->assertSessionHas('pin');
    $response->assertSessionHas('student_name', 'Maria Santos');
    $response->assertSessionHas('student_username', 'msantos');

    $this->assertDatabaseHas('users', [
        'full_name' => 'Maria Santos',
        'username' => 'msantos',
        'role' => UserRole::Student->value,
        'grade' => 5,
        'section' => 'A',
        'teacher_id' => $teacher->id,
        'is_active' => true,
        'must_change_password' => true,
    ]);

    $this->assertDatabaseHas('student_profiles', [
        'lrn' => '123456789012',
    ]);
});

test('created student has hashed password', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Test Student',
        'username' => 'teststudent',
        'lrn' => '111111111111',
        'grade' => 6,
        'section' => 'B',
    ]);

    $student = User::where('username', 'teststudent')->first();
    expect($student->password)->not->toBe('password');
    expect(password_verify('password', $student->password))->toBeFalse();
});

test('temporary password is 8 characters', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Temp Pass Student',
        'username' => 'tempstudent',
        'lrn' => '222222222222',
        'grade' => 5,
        'section' => 'C',
    ]);

    $tempPassword = $response->getSession()->get('temporary_password');
    expect($tempPassword)->toHaveLength(8);
});

test('pin is 6 digits', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Pin Student',
        'username' => 'pinstudent',
        'lrn' => '333333333333',
        'grade' => 6,
        'section' => 'A',
    ]);

    $pin = $response->getSession()->get('pin');
    expect($pin)->toHaveLength(6);
    expect($pin)->toMatch('/^\d{6}$/');
});

test('student profile has hashed pin', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Hashed Pin Student',
        'username' => 'hashedpinstudent',
        'lrn' => '444444444444',
        'grade' => 5,
        'section' => 'B',
    ]);

    $student = User::where('username', 'hashedpinstudent')->first();
    $profile = $student->studentProfile;
    expect($profile)->not->toBeNull();
    expect($profile->lrn)->toBe('444444444444');
    // Pin should be hashed (not the raw 6-digit value)
    expect(mb_strlen($profile->getRawOriginal('pin')))->toBeGreaterThan(6);
});

test('student is linked to the teacher who created it', function (): void {
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Linked Student',
        'username' => 'linkedstudent',
        'lrn' => '555555555555',
        'grade' => 6,
        'section' => 'C',
    ]);

    $student = User::where('username', 'linkedstudent')->first();
    expect($student->teacher_id)->toBe($teacher->id);
    expect($student->teacher->id)->toBe($teacher->id);
});

test('validation requires full_name', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'username' => 'noname',
        'lrn' => '666666666666',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('full_name');
});

test('validation requires username', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'No Username',
        'lrn' => '777777777777',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('username');
});

test('validation requires unique username', function (): void {
    $teacher = User::factory()->teacher()->create();
    User::factory()->create(['username' => 'existing']);

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Duplicate User',
        'username' => 'existing',
        'lrn' => '888888888888',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('username');
});

test('validation requires lrn', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'No LRN',
        'username' => 'nolrn',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('lrn');
});

test('validation requires lrn to be exactly 12 characters', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Short LRN',
        'username' => 'shortlrn',
        'lrn' => '12345',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('lrn');
});

test('validation requires unique lrn', function (): void {
    $teacher = User::factory()->teacher()->create();
    $existingStudent = User::factory()->student()->create();
    $existingStudent->studentProfile()->create([
        'lrn' => '999999999999',
        'pin' => '123456',
    ]);

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Duplicate LRN',
        'username' => 'duplrn',
        'lrn' => '999999999999',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('lrn');
});

test('validation requires valid grade', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'Bad Grade',
        'username' => 'badgrade',
        'lrn' => '101010101010',
        'grade' => 7,
        'section' => 'A',
    ]);

    $response->assertSessionHasErrors('grade');
});

test('validation requires section', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->post(route('teacher.students.store'), [
        'full_name' => 'No Section',
        'username' => 'nosection',
        'lrn' => '121212121212',
        'grade' => 5,
    ]);

    $response->assertSessionHasErrors('section');
});

test('non-teacher cannot POST create student', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->post(route('teacher.students.store'), [
        'full_name' => 'Unauthorized',
        'username' => 'unauthorized',
        'lrn' => '131313131313',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertRedirect(route('login'));
});

test('guest cannot POST create student', function (): void {
    $response = $this->post(route('teacher.students.store'), [
        'full_name' => 'Guest',
        'username' => 'guest',
        'lrn' => '141414141414',
        'grade' => 5,
        'section' => 'A',
    ]);

    $response->assertRedirect(route('login'));
});

test('teacher dashboard renders', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

    $response->assertOk();
});
