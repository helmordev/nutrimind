@extends('layouts.admin')

@section('title', 'NutriMind - Admin Dashboard')

@section('content')
    <div class="mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-blue-600">School Overview</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-600">Monitor staff, students, and active classrooms from one place.</p>
        </div>
        <a href="{{ route('admin.teachers.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
            Create Teacher
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm font-medium text-gray-500">Total Teachers</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $teacherCount }}</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm font-medium text-gray-500">Total Students</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $studentCount }}</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <p class="text-sm font-medium text-gray-500">Active Classes</p>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $activeClassroomCount }}</p>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
        <a href="{{ route('admin.teachers.create') }}" class="block rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-gray-500">Teacher Management</p>
            <h2 class="mt-3 text-xl font-semibold text-gray-900">Create Teacher</h2>
            <p class="mt-2 text-sm text-gray-600">Add a new teacher account and issue a one-time temporary password.</p>
        </a>

        <a href="{{ route('admin.students.index') }}" class="block rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-gray-500">Students</p>
            <h2 class="mt-3 text-xl font-semibold text-gray-900">Read-only Student List</h2>
            <p class="mt-2 text-sm text-gray-600">Review enrolled students, their assigned teachers, grade levels, and LRNs.</p>
        </a>
    </div>
@endsection
