@extends('layouts.teacher')

@section('title', 'Classrooms - NutriMind Teacher')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Classrooms</h1>
            <p class="mt-1 text-sm text-text-secondary">Manage your classroom rooms and codes.</p>
        </div>
        <a href="{{ route('teacher.classrooms.create') }}"
            class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2 text-sm font-bold uppercase tracking-widest text-black transition-colors hover:bg-brand-dark">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Classroom
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-brand/20 bg-brand/5 px-5 py-3">
            <svg class="h-5 w-5 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-brand">{{ session('success') }}</p>
        </div>
    @endif

    @if ($classrooms->isEmpty())
        <div class="rounded-lg bg-surface-card p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
            <h3 class="mt-4 text-sm font-medium text-text-primary">No classrooms yet</h3>
            <p class="mt-1 text-sm text-text-muted">Create your first classroom to start organizing students.</p>
            <a href="{{ route('teacher.classrooms.create') }}" class="mt-4 inline-block text-sm font-semibold text-brand hover:text-brand-dark">Create Classroom &rarr;</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($classrooms as $classroom)
                <a href="{{ route('teacher.classrooms.show', $classroom) }}" class="group block rounded-lg bg-surface-card p-5 transition-colors hover:bg-surface-elevated">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-text-primary group-hover:text-brand">{{ $classroom->name }}</h3>
                            <p class="mt-1 text-xs text-text-muted">Grade {{ $classroom->grade }} — {{ $classroom->section }}</p>
                        </div>
                        <span class="rounded-full bg-surface-elevated px-2.5 py-1 text-xs font-semibold text-text-secondary group-hover:bg-surface-active">
                            {{ $classroom->students_count }} {{ Str::plural('student', $classroom->students_count) }}
                        </span>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wider text-text-muted">Room Code</span>
                        <span class="font-mono text-sm font-bold tracking-widest text-brand">{{ $classroom->room_code }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
