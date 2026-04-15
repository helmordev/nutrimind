<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriMind - Create Classroom</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('teacher.class') }}" class="text-lg font-bold text-gray-800">NutriMind Teacher</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto mt-8 px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Classroom</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.classrooms.store') }}" class="bg-white rounded-lg shadow-md p-6">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Classroom Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g. Grade 5 - Section A" />
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="grade" class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                    <select id="grade" name="grade" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Grade</option>
                        <option value="5" @selected(old('grade') == '5')>Grade 5</option>
                        <option value="6" @selected(old('grade') == '6')>Grade 6</option>
                    </select>
                </div>
                <div>
                    <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                    <input type="text" id="section" name="section" value="{{ old('section') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g. Section A" />
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Create Classroom
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('teacher.classrooms.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Classrooms</a>
        </div>
    </div>
</body>
</html>
