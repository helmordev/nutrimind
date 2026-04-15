<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Requests\WebLoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LoginController
{
    /**
     * Show the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the user via session.
     */
    public function store(WebLoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! Auth::attempt($validated)) {
            return back()
                ->withErrors(['username' => 'The provided credentials are incorrect.'])
                ->onlyInput('username');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['username' => 'This account has been deactivated.'])
                ->onlyInput('username');
        }

        if ($user->role === UserRole::Student) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['username' => 'Students must log in through the mobile app.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return match ($user->role) {
            UserRole::SuperAdmin => redirect()->intended('/admin/dashboard'),
            UserRole::Teacher => redirect()->intended('/teacher/class'),
            default => redirect()->intended('/'),
        };
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
