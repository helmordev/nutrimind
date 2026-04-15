<?php

declare(strict_types=1);

use App\Http\Middleware\EnsurePasswordChanged;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

it('returns json 403 when api user must change password', function (): void {
    $teacher = User::factory()->teacher()->mustChangePassword()->create();

    Route::middleware(['auth:sanctum', EnsurePasswordChanged::class])
        ->get('/__test/password-check', fn (Request $request) => response()->json(['ok' => true]));

    $response = $this->actingAs($teacher)
        ->getJson('/__test/password-check');

    $response->assertForbidden()
        ->assertJson([
            'message' => 'You must change your password before continuing.',
            'must_change_password' => true,
        ]);
});
