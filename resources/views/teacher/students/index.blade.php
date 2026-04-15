@extends('layouts.teacher')

@section('title', 'My Students - NutriMind Teacher')

@section('content')
    <script>window.location = "{{ route('teacher.class') }}";</script>
    <p class="text-sm text-text-secondary">Redirecting to <a href="{{ route('teacher.class') }}" class="text-brand hover:text-brand-dark">My Class</a>...</p>
@endsection
