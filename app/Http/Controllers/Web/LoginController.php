<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Requests\WebLoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        /** @var User|null $user */
        $user = User::query()
            ->where('username', $validated['username'])
            ->first();

        if (! $user instanceof User || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['username' => 'The provided credentials are incorrect.'])
                ->onlyInput('username');
        }

        if (! $user->is_active) {
            return back()
                ->withErrors(['username' => 'This account has been deactivated.'])
                ->onlyInput('username');
        }

        if ($user->role === UserRole::Student) {
            return back()
                ->withErrors(['username' => 'Students must log in through the mobile app.'])
                ->onlyInput('username');
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === UserRole::Teacher && $user->must_change_password) {
            return redirect()->route('teacher.password.edit');
        }

        return match ($user->role) {
            UserRole::SuperAdmin => redirect()->intended('/admin/dashboard'),
            default => redirect()->intended(route('teacher.class')),
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
