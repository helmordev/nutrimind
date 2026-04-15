<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriMind - Create Teacher</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-gray-800">NutriMind Admin</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto mt-8 px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create Teacher Account</h1>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm font-medium text-green-800 mb-2">{{ session('success') }}</p>
                <div class="bg-white rounded p-4 border border-green-300">
                    <p class="text-sm text-gray-600"><strong>Name:</strong> {{ session('teacher_name') }}</p>
                    <p class="text-sm text-gray-600"><strong>Username:</strong> {{ session('teacher_username') }}</p>
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Temporary Password:</strong>
                        <span class="font-mono bg-yellow-100 px-2 py-1 rounded text-lg select-all">{{ session('temporary_password') }}</span>
                    </p>
                    <p class="text-xs text-red-500 mt-2">Please provide this password to the teacher. It will not be shown again.</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.teachers.store') }}" class="bg-white rounded-lg shadow-md p-6">
            @csrf

            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g. Juan Dela Cruz" />
            </div>

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g. jdelacruz" />
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
                Create Teacher Account
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
