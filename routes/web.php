<?php

declare(strict_types=1);

use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:web-login');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role.teacher'])->prefix('teacher')->group(function (): void {
    // Teacher dashboard routes will be added in Task 9+
});

Route::middleware(['auth', 'role.admin'])->prefix('admin')->group(function (): void {
    // Admin dashboard routes will be added in Task 9+
});
