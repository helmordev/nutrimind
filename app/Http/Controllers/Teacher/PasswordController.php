<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Requests\Teacher\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

final class PasswordController
{
    /**
     * Show the forced password change form.
     */
    public function edit(): View
    {
        return view('teacher.change-password');
    }

    /**
     * Update the teacher's password and clear the forced change flag.
     */
    public function update(ChangePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return to_route('teacher.class')
            ->with('success', 'Password changed successfully.');
    }
}
