<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIsTeacher
{
    /**
     * Ensure the authenticated user has the Teacher role.
     *
     * Web requests are redirected to the login page;
     * API requests receive a 403 JSON response.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user?->role !== UserRole::Teacher) {
            abort_if($request->expectsJson(), 403, 'This action requires a teacher account.');

            return to_route('login');
        }

        return $next($request);
    }
}
