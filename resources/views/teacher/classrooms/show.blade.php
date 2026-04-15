<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriMind - {{ $classroom->name }}</title>
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
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $classroom->name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">Grade {{ $classroom->grade }} &bull; {{ $classroom->section }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Room Code</p>
                    <span class="font-mono bg-blue-100 text-blue-800 px-3 py-2 rounded text-2xl font-bold select-all">{{ $classroom->room_code }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Students ({{ $classroom->students->count() }})</h2>
            </div>

            @if ($classroom->students->isEmpty())
                <p class="text-gray-500 text-sm">No students in this classroom yet. Create students and assign them to this classroom.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($classroom->students as $student)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $student->full_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $student->username }}</td>
                                    <td class="px-4 py-3">
                                        @if ($student->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('teacher.classrooms.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Classrooms</a>
        </div>
    </div>
</body>
</html>
