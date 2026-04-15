<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

test('phase 1 account chain works end to end', function (): void {
    $this->seed(DatabaseSeeder::class);

    /** @var User $admin */
    $admin = User::query()->where('username', 'registrar')->firstOrFail();
    $admin->forceFill([
        'password' => Hash::make('password'),
    ])->save();

    $adminLogin = $this->post(route('login'), [
        'username' => 'registrar',
        'password' => 'password',
    ]);

    expect($adminLogin->status())->toBe(302);
    $this->assertAuthenticatedAs($admin);
    expect(Auth::user()?->role)->toBe(UserRole::SuperAdmin);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Dashboard');

    $teacherCreate = $this->actingAs($admin)->post(route('admin.teachers.store'), [
        'full_name' => 'Phase One Teacher',
        'username' => 'phaseoneteacher',
        'grade' => 5,
        'section' => 'Section A',
    ]);

    expect($teacherCreate->status())->toBe(302);
    expect($teacherCreate->headers->get('Location'))->toEndWith('/admin/teachers/create');

    $teacherCreate->assertSessionHas('temporary_password');

    $temporaryPassword = $teacherCreate->getSession()->get('temporary_password');

    expect($temporaryPassword)->toBeString()->toHaveLength(8);

    $this->post(route('logout'))->assertRedirect('/login');
    $this->assertGuest();

    $teacherLogin = $this->post(route('login'), [
        'username' => 'phaseoneteacher',
        'password' => $temporaryPassword,
    ]);

    expect($teacherLogin->status())->toBe(302);
    expect($teacherLogin->headers->get('Location'))->toEndWith('/teacher/change-password');

    /** @var User $teacher */
    $teacher = User::query()->where('username', 'phaseoneteacher')->firstOrFail();

    $passwordChange = $this->actingAs($teacher)
        ->post(route('teacher.password.update'), [
            'current_password' => $temporaryPassword,
            'password' => 'updated-password',
            'password_confirmation' => 'updated-password',
        ]);

    expect($passwordChange->status())->toBe(302);
    expect($passwordChange->headers->get('Location'))->toEndWith('/teacher/class');

    $passwordChange->assertSessionHas('success', 'Password changed successfully.');

    $studentCreate = $this->actingAs($teacher->fresh())
        ->post(route('teacher.students.store'), [
            'full_name' => 'Phase One Student',
            'username' => 'phaseonestudent',
            'lrn' => '123456789012',
            'grade' => 5,
            'section' => 'Section A',
        ]);

    expect($studentCreate->status())->toBe(302);
    expect($studentCreate->headers->get('Location'))->toEndWith('/teacher/students/create');

    $studentCreate->assertSessionHas('pin');

    $studentPin = $studentCreate->getSession()->get('pin');

    $this->post(route('logout'))->assertRedirect('/login');

    $studentLogin = $this->postJson('/api/v1/auth/login', [
        'lrn' => '123456789012',
        'pin' => $studentPin,
    ]);

    $studentLogin
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'student' => ['id', 'full_name', 'grade', 'section', 'must_change_password'],
        ]);

    $token = $studentLogin->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/student/worlds')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/student/sync/state')
        ->assertOk()
        ->assertJsonStructure([
            'worlds',
            'preferences',
            'difficulties',
            'screen_time',
            'badges',
            'grades',
        ])
        ->assertJsonCount(3, 'worlds');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/admin/dashboard')
        ->assertForbidden();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/teacher/class')
        ->assertForbidden();

    $this->actingAs($teacher->fresh())
        ->get('/admin/dashboard')
        ->assertRedirect(route('login'));

    /** @var User $student */
    $student = User::query()->where('username', 'phaseonestudent')->firstOrFail();
    /** @var StudentProfile $studentProfile */
    $studentProfile = $student->studentProfile()->firstOrFail();

    expect($studentProfile->lrn)->toBe('123456789012');
    expect($studentPin)->toBeString()->toHaveLength(6);
});

test('student login endpoint is rate limited on the sixth rapid attempt', function (): void {
    $student = User::factory()->student()->create();

    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'lrn' => '987654321098',
        'pin' => '123456',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/login', [
            'lrn' => '987654321098',
            'pin' => '000000',
        ])->assertUnauthorized();
    }

    $this->postJson('/api/v1/auth/login', [
        'lrn' => '987654321098',
        'pin' => '000000',
    ])->assertTooManyRequests();
});
