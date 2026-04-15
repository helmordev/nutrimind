<?php

declare(strict_types=1);

use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\StudentLoginController;
use App\Http\Controllers\Student\JoinRoomController;
use App\Http\Controllers\Student\SyncController;
use App\Http\Controllers\Student\WorldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', StudentLoginController::class)
            ->middleware('throttle:student-login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/logout', LogoutController::class);
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', fn (Request $request) => $request->user());
    });
});

Route::middleware(['auth:sanctum', 'role.student'])->prefix('v1/student')->group(function (): void {
    Route::get('/worlds', [WorldController::class, 'index']);
    Route::get('/sync/state', SyncController::class);
    Route::post('/join-room', JoinRoomController::class);
});
