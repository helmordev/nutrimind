<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriMind - My Classrooms</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('teacher.dashboard') }}" class="text-lg font-bold text-gray-800">NutriMind Teacher</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">My Classrooms</h1>
            <a href="{{ route('teacher.classrooms.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors">
                + New Classroom
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if ($classrooms->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-500">No classrooms yet. Create your first classroom to get started.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($classrooms as $classroom)
                    <a href="{{ route('teacher.classrooms.show', $classroom) }}"
                        class="block bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800">{{ $classroom->name }}</h2>
                                <p class="text-sm text-gray-500 mt-1">Grade {{ $classroom->grade }} &bull; {{ $classroom->section }}</p>
                            </div>
                            <span class="font-mono bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-bold">{{ $classroom->room_code }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-3">{{ $classroom->students_count }} {{ Str::plural('student', $classroom->students_count) }}</p>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('teacher.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
