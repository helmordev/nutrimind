<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NutriMind - Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <nav class="border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-gray-800">NutriMind Admin</a>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-blue-600">Dashboard</a>
                <a href="{{ route('admin.teachers.create') }}" class="text-gray-600 hover:text-blue-600">Create Teacher</a>
                <a href="{{ route('admin.students.index') }}" class="text-gray-600 hover:text-blue-600">Students</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
