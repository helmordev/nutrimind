<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - NutriMind</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-surface-base">
    <div class="w-full max-w-sm px-4">
        {{-- Brand --}}
        <div class="mb-8 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand">
                <svg class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563a6 6 0 1111.162-8.279z"/></svg>
            </div>
            <h1 class="mt-4 text-xl font-bold text-text-primary">Change Your Password</h1>
            <p class="mt-1 text-sm text-text-secondary">You must change your temporary password before continuing.</p>
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
            <form method="POST" action="{{ route('teacher.password.update') }}" class="space-y-5">
                @csrf

                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="mb-1.5 block text-sm font-medium text-text-secondary">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required
                        class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-text-secondary">New Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand">
                    <p class="mt-1 text-xs text-text-muted">Minimum 8 characters.</p>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-text-secondary">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full rounded-md border border-border-default bg-surface-elevated px-3 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none transition-colors focus:border-brand focus:ring-1 focus:ring-brand">
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full rounded-full bg-brand px-6 py-2.5 text-sm font-bold uppercase tracking-widest text-black transition-colors hover:bg-brand-dark">
                    Change Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
