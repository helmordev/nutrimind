<?php

declare(strict_types=1);

use App\Models\Classroom;
use App\Models\User;

it('forces a new teacher to change their password before accessing class', function (): void {
    User::factory()->teacher()->mustChangePassword()->create([
        'username' => 'teacher_pwd',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_pwd')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/change-password')
        ->assertSee('Change Your Password')
        ->assertSee('You must change your temporary password before continuing.')
        ->type('current_password', 'password')
        ->type('password', 'newpassword123')
        ->type('password_confirmation', 'newpassword123')
        ->press('Change Password')
        ->assertPathIs('/teacher/class')
        ->assertSee('Password changed successfully');
});

it('shows the teacher class page with students', function (): void {
    $teacher = User::factory()->teacher()->create([
        'username' => 'teacher_class',
        'password' => 'password',
    ]);

    User::factory()->create([
        'full_name' => 'Alice Student',
        'teacher_id' => $teacher->id,
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_class')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/class')
        ->assertSee('My Class')
        ->assertSee('Alice Student');
});

it('shows the empty class state for a teacher with no students', function (): void {
    User::factory()->teacher()->create([
        'username' => 'teacher_empty',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_empty')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/class')
        ->assertSee('No students yet')
        ->assertSee('Create your first student to get started');
});

it('lets a teacher create a student and see the credentials', function (): void {
    User::factory()->teacher()->create([
        'username' => 'teacher_create_s',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_create_s')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/class')
        ->click('Add Student')
        ->assertPathIs('/teacher/students/create')
        ->assertSee('Create Student')
        ->type('full_name', 'New Student')
        ->type('username', 'newstudent')
        ->type('lrn', '123456789012')
        ->select('grade', '5')
        ->type('section', 'Rose')
        ->click('button.bg-brand')
        ->assertPathIs('/teacher/students/create')
        ->assertSee('Student account created successfully');
});

it('lets a teacher navigate to the classrooms page', function (): void {
    $teacher = User::factory()->teacher()->create([
        'username' => 'teacher_rooms',
        'password' => 'password',
    ]);

    Classroom::factory()->create([
        'teacher_id' => $teacher->id,
        'name' => 'Morning Class',
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_rooms')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/class')
        ->click('Classrooms')
        ->assertPathIs('/teacher/classrooms')
        ->assertSee('Classrooms')
        ->assertSee('Morning Class');
});

it('lets a teacher create a classroom and view it', function (): void {
    User::factory()->teacher()->create([
        'username' => 'teacher_create_c',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_create_c')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/class')
        ->click('Classrooms')
        ->assertPathIs('/teacher/classrooms')
        ->click('New Classroom')
        ->assertPathIs('/teacher/classrooms/create')
        ->assertSee('Create Classroom')
        ->type('name', 'Afternoon Class')
        ->select('grade', '6')
        ->type('section', 'Dahlia')
        ->click('button.bg-brand')
        ->assertSee('Classroom created successfully')
        ->assertSee('Afternoon Class')
        ->assertSee('Room Code');
});

it('shows the empty classrooms state for a teacher with no classrooms', function (): void {
    User::factory()->teacher()->create([
        'username' => 'teacher_no_rooms',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->type('username', 'teacher_no_rooms')
        ->type('password', 'password')
        ->press('Sign In')
        ->assertPathIs('/teacher/class')
        ->click('Classrooms')
        ->assertPathIs('/teacher/classrooms')
        ->assertSee('No classrooms yet')
        ->assertSee('Create your first classroom to start organizing students');
});
