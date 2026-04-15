<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ClassController
{
    public function index(Request $request): View
    {
        /** @var User $teacher */
        $teacher = $request->user();

        $students = $teacher->students()
            ->with('studentProfile')
            ->orderBy('full_name')
            ->get();

        return view('teacher.class', [
            'students' => $students,
        ]);
    }
}
