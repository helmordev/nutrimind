<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'NutriMind Teacher' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('teacher.class') }}" class="text-lg font-bold text-gray-800">NutriMind Teacher</a>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('teacher.class') }}" class="text-gray-600 hover:text-blue-700">My Students</a>
                    <a href="{{ route('teacher.students.create') }}" class="text-gray-600 hover:text-blue-700">Create Student</a>
                    <a href="{{ route('teacher.password.edit') }}" class="text-gray-600 hover:text-blue-700">Change Password</a>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
            </form>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto mt-8 px-4">
        @yield('content')
    </main>
</body>
</html>
