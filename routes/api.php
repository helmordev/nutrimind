<?php

declare(strict_types=1);

use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\StudentLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/student/login', StudentLoginController::class)
    ->middleware('throttle:student-login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', LogoutController::class);
});
