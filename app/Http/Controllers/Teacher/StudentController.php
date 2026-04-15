<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Enums\UserRole;
use App\Http\Requests\Teacher\CreateStudentRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class StudentController
{
    public function create(): View
    {
        return view('teacher.students.create');
    }

    public function store(CreateStudentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $temporaryPassword = Str::random(8);
        $pin = mb_str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        /** @var User $teacher */
        $teacher = $request->user();

        $student = DB::transaction(function () use ($validated, $temporaryPassword, $pin, $teacher): User {
            $student = User::create([
                'role' => UserRole::Student,
                'full_name' => $validated['full_name'],
                'username' => $validated['username'],
                'password' => $temporaryPassword,
                'grade' => $validated['grade'],
                'section' => $validated['section'],
                'teacher_id' => $teacher->id,
                'is_active' => true,
                'must_change_password' => true,
            ]);

            StudentProfile::create([
                'user_id' => $student->id,
                'lrn' => $validated['lrn'],
                'pin' => $pin,
                'pin_generated_at' => now(),
            ]);

            return $student;
        });

        return redirect()
            ->route('teacher.students.create')
            ->with('success', 'Student account created successfully.')
            ->with('temporary_password', $temporaryPassword)
            ->with('pin', $pin)
            ->with('student_name', $validated['full_name'])
            ->with('student_username', $validated['username']);
    }
}
