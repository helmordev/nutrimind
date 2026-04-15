@extends('layouts.teacher')

@section('title', '{{ $classroom->name }} - NutriMind Teacher')

@section('content')
    <div class="mb-8">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.classrooms.index') }}" class="text-text-muted transition-colors hover:text-text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-text-primary">{{ $classroom->name }}</h1>
                <p class="mt-1 text-sm text-text-secondary">Grade {{ $classroom->grade }} — {{ $classroom->section }}</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-brand/20 bg-brand/5 px-5 py-3">
            <svg class="h-5 w-5 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-brand">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Room Code Card --}}
    <div class="mb-8 rounded-lg bg-surface-card p-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-text-muted">Room Code</p>
        <p class="mt-2 font-mono text-4xl font-bold tracking-[0.3em] text-brand">{{ $classroom->room_code }}</p>
        <p class="mt-2 text-xs text-text-muted">Share this code with students to join this classroom.</p>
    </div>

    {{-- Students Table --}}
    <div class="rounded-lg bg-surface-card">
        <div class="border-b border-border-subtle px-5 py-4">
            <h2 class="text-sm font-bold text-text-primary">Students ({{ $classroom->students->count() }})</h2>
        </div>

        @if ($classroom->students->isEmpty())
            <div class="p-8 text-center">
                <p class="text-sm text-text-muted">No students have joined this classroom yet.</p>
            </div>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-subtle">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Username</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Grade</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Section</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle">
                    @foreach ($classroom->students as $student)
                        <tr class="transition-colors hover:bg-surface-hover">
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-text-primary">{{ $student->full_name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-text-secondary">{{ $student->username }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->grade ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->section ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
