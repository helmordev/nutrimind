<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Contracts\View\View;

final class DashboardController
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'teacherCount' => User::query()->where('role', UserRole::Teacher)->count(),
            'studentCount' => User::query()->where('role', UserRole::Student)->count(),
            'activeClassroomCount' => Classroom::query()->where('is_active', true)->count(),
        ]);
    }
}
