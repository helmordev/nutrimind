<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\StudentController;
use App\Http\Controllers\Web\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:web-login');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role.teacher'])->prefix('teacher')->name('teacher.')->group(function (): void {
    Route::view('/dashboard', 'teacher.dashboard')->name('dashboard');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
    Route::get('/classrooms/create', [ClassroomController::class, 'create'])->name('classrooms.create');
    Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
    Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show'])->name('classrooms.show');
});

Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
});
