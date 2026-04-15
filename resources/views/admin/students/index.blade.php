@extends('layouts.admin')

@section('title', 'NutriMind - Students')

@section('content')
    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-blue-600">Students</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Read-only Student List</h1>
            <p class="mt-2 text-sm text-gray-600">A school-wide view of student records for admin oversight.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Back to Dashboard</a>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        @if ($students->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">No students have been created yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">LRN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Grade</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Section</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Teacher</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($students as $student)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $student->full_name }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $student->studentProfile?->lrn ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $student->grade }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $student->section }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $student->teacher?->full_name ?? 'Unassigned' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
