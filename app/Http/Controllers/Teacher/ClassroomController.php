<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Requests\Teacher\CreateClassroomRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ClassroomController
{
    public function index(Request $request): View
    {
        /** @var User $teacher */
        $teacher = $request->user();

        $classrooms = $teacher->classrooms()
            ->withCount('students')
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.classrooms.index', compact('classrooms'));
    }

    public function create(): View
    {
        return view('teacher.classrooms.create');
    }

    public function store(CreateClassroomRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        /** @var User $teacher */
        $teacher = $request->user();

        $classroom = Classroom::create([
            'teacher_id' => $teacher->id,
            'name' => $validated['name'],
            'grade' => $validated['grade'],
            'section' => $validated['section'],
            'room_code' => Classroom::generateRoomCode(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('teacher.classrooms.show', $classroom)
            ->with('success', 'Classroom created successfully.');
    }

    public function show(Request $request, Classroom $classroom): View
    {
        /** @var User $teacher */
        $teacher = $request->user();

        abort_if($classroom->teacher_id !== $teacher->id, 403);

        $classroom->load('students');

        return view('teacher.classrooms.show', compact('classroom'));
    }
}
