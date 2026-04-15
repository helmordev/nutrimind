@extends('layouts.teacher')

@section('title', 'My Class - NutriMind Teacher')

@section('content')
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">My Class</h1>
            <p class="mt-1 text-sm text-text-secondary">Students assigned to you.</p>
        </div>
        <a href="{{ route('teacher.students.create') }}"
            class="inline-flex items-center gap-2 rounded-full bg-brand px-5 py-2 text-sm font-bold uppercase tracking-widest text-black transition-colors hover:bg-brand-dark">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Student
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-brand/20 bg-brand/5 px-5 py-3">
            <svg class="h-5 w-5 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium text-brand">{{ session('success') }}</p>
        </div>
    @endif

    @if ($students->isEmpty())
        <div class="rounded-lg bg-surface-card p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            <h3 class="mt-4 text-sm font-medium text-text-primary">No students yet</h3>
            <p class="mt-1 text-sm text-text-muted">Create your first student to get started.</p>
            <a href="{{ route('teacher.students.create') }}" class="mt-4 inline-block text-sm font-semibold text-brand hover:text-brand-dark">Create Student &rarr;</a>
        </div>
    @else
        <div class="overflow-hidden rounded-lg bg-surface-card">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-subtle">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Username</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">LRN</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Grade</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Section</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle">
                    @foreach ($students as $student)
                        <tr class="transition-colors hover:bg-surface-hover">
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-text-primary">{{ $student->full_name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-text-secondary">{{ $student->username }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-text-secondary">{{ $student->studentProfile?->lrn ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->grade ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->section ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
