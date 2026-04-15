<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NutriMind - Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-base text-text-primary">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-30 flex w-60 flex-col bg-surface-card">
            {{-- Brand --}}
            <div class="flex h-16 items-center gap-3 px-5">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand">
                    <svg class="h-4 w-4 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span class="text-sm font-bold tracking-wide text-text-primary">NUTRIMIND</span>
                <span class="rounded-full bg-brand/20 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-brand">Admin</span>
            </div>

            {{-- Navigation --}}
            <nav class="mt-4 flex flex-1 flex-col gap-1 px-3">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-surface-active text-text-primary' : 'text-text-secondary hover:bg-surface-elevated hover:text-text-primary' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.teachers.create') }}"
                    class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.teachers.*') ? 'bg-surface-active text-text-primary' : 'text-text-secondary hover:bg-surface-elevated hover:text-text-primary' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                    Create Teacher
                </a>

                <a href="{{ route('admin.students.index') }}"
                    class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.students.*') ? 'bg-surface-active text-text-primary' : 'text-text-secondary hover:bg-surface-elevated hover:text-text-primary' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Students
                </a>
            </nav>

            {{-- User / Logout --}}
            <div class="border-t border-border-subtle px-3 py-4">
                <div class="mb-3 px-3">
                    <p class="truncate text-sm font-medium text-text-primary">{{ auth()->user()->full_name }}</p>
                    <p class="truncate text-xs text-text-muted">Administrator</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-negative transition-colors hover:bg-surface-elevated">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="ml-60 flex-1 p-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
