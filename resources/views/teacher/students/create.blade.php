@extends('layouts.teacher')

@section('title', 'Create Student - NutriMind Teacher')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-text-primary">Create Student</h1>
        <p class="mt-1 text-sm text-text-secondary">Register a new student to your class.</p>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="mb-6 rounded-lg border border-brand/20 bg-brand/5 p-5">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium text-brand">{{ session('success') }}</p>
            </div>
            <div class="mt-4 rounded-md bg-surface-card p-4">
                <p class="text-sm text-text-secondary">Student credentials — share these securely:</p>
                <div class="mt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider text-text-muted">Name</span>
                        <span class="font-mono text-sm text-text-primary">{{ session('student_name') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider text-text-muted">Username</span>
                        <span class="font-mono text-sm text-text-primary">{{ session('student_username') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider text-text-muted">Temporary Password</span>
                        <span class="font-mono text-sm font-bold text-warning">{{ session('temporary_password') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs uppercase tracking-wider text-text-muted">PIN</span>
                        <span class="font-mono text-sm font-bold text-warning">{{ session('pin') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <div class="max-w-lg rounded-lg bg-surface-card p-6">
        <form method="POST" action="{{ route('teacher.students.store') }}" class="space-y-5">
            @csrf

            {{-- Full Name --}}
            <div>
                <label for="full_name" class="mb-1.5 block text-sm font-medium text-text-secondary">Full Name</label>
                <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required
                    class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                    placeholder="e.g. Maria Santos">
                @error('full_name')
                    <p class="mt-1 text-xs text-negative">{{ $message }}</p>
                @enderror
            </div>

            {{-- Username --}}
            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-text-secondary">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required
                    class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                    placeholder="e.g. msantos">
                @error('username')
                    <p class="mt-1 text-xs text-negative">{{ $message }}</p>
                @enderror
            </div>

            {{-- LRN --}}
            <div>
                <label for="lrn" class="mb-1.5 block text-sm font-medium text-text-secondary">LRN (Learner Reference Number)</label>
                <input type="text" name="lrn" id="lrn" value="{{ old('lrn') }}" required maxlength="12"
                    class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 font-mono text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                    placeholder="e.g. 123456789012">
                @error('lrn')
                    <p class="mt-1 text-xs text-negative">{{ $message }}</p>
                @enderror
            </div>

            {{-- Grade --}}
            <div>
                <label for="grade" class="mb-1.5 block text-sm font-medium text-text-secondary">Grade</label>
                <select name="grade" id="grade" required
                    class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand">
                    <option value="" class="text-text-muted">Select Grade</option>
                    <option value="5" {{ old('grade') == '5' ? 'selected' : '' }}>Grade 5</option>
                    <option value="6" {{ old('grade') == '6' ? 'selected' : '' }}>Grade 6</option>
                </select>
                @error('grade')
                    <p class="mt-1 text-xs text-negative">{{ $message }}</p>
                @enderror
            </div>

            {{-- Section --}}
            <div>
                <label for="section" class="mb-1.5 block text-sm font-medium text-text-secondary">Section</label>
                <input type="text" name="section" id="section" value="{{ old('section') }}" required
                    class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                    placeholder="e.g. Sampaguita">
                @error('section')
                    <p class="mt-1 text-xs text-negative">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full rounded-full bg-brand px-6 py-2.5 text-sm font-bold uppercase tracking-widest text-black transition-colors hover:bg-brand-dark">
                Create Student
            </button>
        </form>
    </div>
@endsection
