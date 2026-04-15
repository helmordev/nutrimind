<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NutriMind</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-surface-base">
    <div class="w-full max-w-sm px-4">
        {{-- Brand --}}
        <div class="mb-8 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand">
                <svg class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h1 class="mt-4 text-xl font-bold text-text-primary">NutriMind</h1>
            <p class="mt-1 text-sm text-text-secondary">Sign in to your account.</p>
        </div>

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-negative/20 bg-negative/5 px-4 py-3">
                @foreach ($errors->all() as $error)
                    <p class="text-xs text-negative">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Form --}}
        <div class="rounded-lg bg-surface-card p-6">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-text-secondary">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                        class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                        placeholder="Enter your username">
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-text-secondary">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand"
                        placeholder="Enter your password">
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full rounded-full bg-brand px-6 py-2.5 text-sm font-bold uppercase tracking-widest text-black transition-colors hover:bg-brand-dark">
                    Sign In
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-text-muted">Students should log in through the mobile app.</p>
    </div>
</body>
</html>
