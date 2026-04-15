@extends('layouts.admin')

@section('title', 'Dashboard - NutriMind Admin')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-text-primary">Dashboard</h1>
        <p class="mt-1 text-sm text-text-secondary">Welcome back. Here's an overview of NutriMind.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Teachers --}}
        <div class="rounded-lg bg-surface-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider text-text-muted">Teachers</p>
                    <p class="mt-2 text-3xl font-bold text-text-primary">{{ $teacherCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10">
                    <svg class="h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.teachers.create') }}" class="mt-4 inline-block text-xs font-semibold uppercase tracking-widest text-brand hover:text-brand-dark">
                Create Teacher &rarr;
            </a>
        </div>

        {{-- Students --}}
        <div class="rounded-lg bg-surface-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider text-text-muted">Students</p>
                    <p class="mt-2 text-3xl font-bold text-text-primary">{{ $studentCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-info/10">
                    <svg class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.students.index') }}" class="mt-4 inline-block text-xs font-semibold uppercase tracking-widest text-info hover:text-info/80">
                View All &rarr;
            </a>
        </div>

        {{-- Active Classrooms --}}
        <div class="rounded-lg bg-surface-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wider text-text-muted">Active Classrooms</p>
                    <p class="mt-2 text-3xl font-bold text-text-primary">{{ $activeClassroomCount }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-warning/10">
                    <svg class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </div>
            </div>
        </div>
    </div>
@endsection
