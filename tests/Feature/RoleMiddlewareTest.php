<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureIsStudent;
use App\Http\Middleware\EnsureIsSuperAdmin;
use App\Http\Middleware\EnsureIsTeacher;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::middleware(['auth:sanctum', EnsureIsStudent::class])
        ->get('/test/student-only', fn () => response()->json(['ok' => true]));

    Route::middleware(['auth', EnsureIsTeacher::class])
        ->get('/test/teacher-only', fn () => response()->json(['ok' => true]));

    Route::middleware(['auth', EnsureIsSuperAdmin::class])
        ->get('/test/admin-only', fn () => response()->json(['ok' => true]));

    Route::middleware(['auth', EnsureIsTeacher::class])
        ->get('/test/teacher-web', fn (): Factory|View => view('auth.login'));

    Route::middleware(['auth', EnsureIsTeacher::class, EnsurePasswordChanged::class])
        ->get('/test/teacher-password-changed', fn () => response()->json(['ok' => true]));

    Route::middleware(['auth', EnsureIsSuperAdmin::class])
        ->get('/test/admin-web', fn (): Factory|View => view('auth.login'));
});

// ── EnsureIsStudent ─────────────────────────────────────────────

test('student can access student-only API route', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student, 'sanctum')
        ->getJson('/test/student-only');

    $response->assertOk()
        ->assertJson(['ok' => true]);
});

test('teacher cannot access student-only API route', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher, 'sanctum')
        ->getJson('/test/student-only');

    $response->assertForbidden();
});

test('super admin cannot access student-only API route', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/test/student-only');

    $response->assertForbidden();
});

test('unauthenticated user cannot access student-only API route', function (): void {
    $response = $this->getJson('/test/student-only');

    $response->assertUnauthorized();
});

// ── EnsureIsTeacher ─────────────────────────────────────────────

test('teacher can access teacher-only route', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)
        ->getJson('/test/teacher-only');

    $response->assertOk()
        ->assertJson(['ok' => true]);
});

test('student cannot access teacher-only API route and gets 403', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)
        ->getJson('/test/teacher-only');

    $response->assertForbidden();
});

test('super admin cannot access teacher-only route', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)
        ->getJson('/test/teacher-only');

    $response->assertForbidden();
});

test('student accessing teacher web route gets redirected to login', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)
        ->get('/test/teacher-web');

    $response->assertRedirect(route('login'));
});

test('unauthenticated user accessing teacher web route gets redirected to login', function (): void {
    $response = $this->get('/test/teacher-web');

    $response->assertRedirect(route('login'));
});

test('teacher with forced password change is redirected from protected teacher routes', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create();

    $response = $this->actingAs($teacher)
        ->get('/test/teacher-password-changed');

    $response->assertRedirect(route('teacher.password.edit'));
});

test('teacher without forced password change can access protected teacher routes', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)
        ->getJson('/test/teacher-password-changed');

    $response->assertOk()
        ->assertJson(['ok' => true]);
});

// ── EnsureIsSuperAdmin ──────────────────────────────────────────

test('super admin can access admin-only route', function (): void {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)
        ->getJson('/test/admin-only');

    $response->assertOk()
        ->assertJson(['ok' => true]);
});

test('teacher cannot access admin-only API route and gets 403', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)
        ->getJson('/test/admin-only');

    $response->assertForbidden();
});

test('student cannot access admin-only API route and gets 403', function (): void {
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)
        ->getJson('/test/admin-only');

    $response->assertForbidden();
});

test('teacher accessing admin web route gets redirected to login', function (): void {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)
        ->get('/test/admin-web');

    $response->assertRedirect(route('login'));
});

test('unauthenticated user accessing admin web route gets redirected to login', function (): void {
    $response = $this->get('/test/admin-web');

    $response->assertRedirect(route('login'));
});

// ── Middleware aliases are registered ───────────────────────────

test('role middleware aliases are registered in the application', function (): void {
    /** @var Router $router */
    $router = resolve(Router::class);
    $aliases = $router->getMiddleware();

    expect($aliases)
        ->toHaveKey('role.student', EnsureIsStudent::class)
        ->toHaveKey('role.teacher', EnsureIsTeacher::class)
        ->toHaveKey('role.admin', EnsureIsSuperAdmin::class)
        ->toHaveKey('password.changed', EnsurePasswordChanged::class);
});
