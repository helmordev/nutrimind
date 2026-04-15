@extends('layouts.admin')

@section('title', 'Students - NutriMind Admin')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-text-primary">All Students</h1>
        <p class="mt-1 text-sm text-text-secondary">A read-only list of all students registered in NutriMind.</p>
    </div>

    @if ($students->isEmpty())
        <div class="rounded-lg bg-surface-card p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            <h3 class="mt-4 text-sm font-medium text-text-primary">No students yet</h3>
            <p class="mt-1 text-sm text-text-muted">Students will appear here once teachers create them.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-lg bg-surface-card">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-subtle">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">LRN</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Grade</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Section</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-muted">Teacher</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-subtle">
                    @foreach ($students as $student)
                        <tr class="transition-colors hover:bg-surface-hover">
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-text-primary">{{ $student->full_name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-sm text-text-secondary">{{ $student->studentProfile?->lrn ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->grade ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->section ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-sm text-text-secondary">{{ $student->teacher?->full_name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
