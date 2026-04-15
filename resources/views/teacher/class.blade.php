@extends('layouts.teacher')

@section('content')
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Class</h1>
            <p class="text-sm text-gray-500 mt-1">View the students assigned to your account and jump to the next teacher actions.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('teacher.students.create') }}"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Create Student
            </a>
            <a href="{{ route('teacher.password.edit') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Change Password
            </a>
            <a href="{{ route('teacher.classrooms.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                My Classrooms
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Students ({{ $students->count() }})</h2>
        </div>

        @if ($students->isEmpty())
            <div class="px-6 py-8 text-sm text-gray-500">
                No students assigned yet. Create a student account to start building your class list.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">LRN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($students as $student)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $student->full_name }}</td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $student->studentProfile?->lrn ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">Grade {{ $student->grade }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $student->section }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
