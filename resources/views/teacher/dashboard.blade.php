<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriMind - Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <span class="text-lg font-bold text-gray-800">NutriMind Teacher</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto mt-8 px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Teacher Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('teacher.classrooms.index') }}"
                class="block bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h2 class="text-lg font-semibold text-gray-800">My Classrooms</h2>
                <p class="text-sm text-gray-500 mt-1">Manage your classrooms and view room codes.</p>
            </a>

            <a href="{{ route('teacher.students.create') }}"
                class="block bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h2 class="text-lg font-semibold text-gray-800">Create Student</h2>
                <p class="text-sm text-gray-500 mt-1">Add a new student account with a temporary password and PIN.</p>
            </a>
        </div>
    </div>
</body>
</html>
