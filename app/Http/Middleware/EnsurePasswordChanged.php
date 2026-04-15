<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePasswordChanged
{
    /**
     * Redirect users who must change their password before accessing the application.
     *
     * Web requests are redirected to the password change form;
     * API requests receive a 403 JSON response.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user?->must_change_password) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must change your password before continuing.',
                    'must_change_password' => true,
                ], Response::HTTP_FORBIDDEN);
            }

            return to_route('teacher.password.edit');
        }

        return $next($request);
    }
}
