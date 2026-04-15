<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriMind - Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-4">Change Your Password</h1>
        <p class="text-center text-sm text-gray-500 mb-8">You must update your temporary password before continuing.</p>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.password.update') }}">
            @csrf

            <div class="mb-4">
                <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">Current Password</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your current password"
                />
            </div>

            <div class="mb-4">
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter a new password"
                />
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Confirm your new password"
                />
            </div>

            <button
                type="submit"
                class="w-full rounded-md bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Update Password
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full text-sm text-red-600 hover:text-red-800">Logout</button>
        </form>
    </div>
</body>
</html>
