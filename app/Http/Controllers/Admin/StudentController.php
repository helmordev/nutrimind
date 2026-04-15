<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\View\View;

final class StudentController
{
    public function index(): View
    {
        $students = User::query()
            ->where('role', UserRole::Student)
            ->with(['studentProfile', 'teacher'])
            ->orderBy('full_name')
            ->get();

        return view('admin.students.index', [
            'students' => $students,
        ]);
    }
}
