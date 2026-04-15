<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\CreateTeacherRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

final class TeacherController
{
    /**
     * Show the form for creating a new teacher.
     */
    public function create(): View
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created teacher in the database.
     */
    public function store(CreateTeacherRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $temporaryPassword = Str::random(8);

        User::create([
            'role' => UserRole::Teacher,
            'full_name' => $validated['full_name'],
            'username' => $validated['username'],
            'password' => $temporaryPassword,
            'grade' => $validated['grade'],
            'section' => $validated['section'],
            'is_active' => true,
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.teachers.create')
            ->with('success', 'Teacher account created successfully.')
            ->with('temporary_password', $temporaryPassword)
            ->with('teacher_name', $validated['full_name'])
            ->with('teacher_username', $validated['username']);
    }
}
