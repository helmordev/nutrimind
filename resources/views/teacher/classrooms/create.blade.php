@extends('layouts.teacher')

@section('title', 'Create Classroom - NutriMind Teacher')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-text-primary">Create Classroom</h1>
        <p class="mt-1 text-sm text-text-secondary">Set up a new classroom with a unique room code.</p>
    </div>

    {{-- Form --}}
    <div class="max-w-lg rounded-lg bg-surface-card p-6">
        <form method="POST" action="{{ route('teacher.classrooms.store') }}" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-text-secondary">Classroom Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                    placeholder="e.g. Math Class A">
                @error('name')
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
                Create Classroom
            </button>
        </form>
    </div>
@endsection
