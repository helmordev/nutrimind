@extends('layouts.teacher')

@section('content')
    <div class="rounded-lg border border-blue-100 bg-blue-50 px-6 py-5 text-sm text-blue-900">
        <p class="font-medium">Student list moved to the class page.</p>
        <p class="mt-1">Use the link below if your browser does not redirect automatically.</p>
        <a href="{{ route('teacher.class') }}" class="mt-3 inline-flex text-blue-700 hover:text-blue-900">
            Go to My Class
        </a>
    </div>
@endsection
