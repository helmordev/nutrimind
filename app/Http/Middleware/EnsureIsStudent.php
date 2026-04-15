<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureIsStudent
{
    /**
     * Ensure the authenticated user has the Student role.
     *
     * Students only access the system via API (mobile app),
     * so unauthorized access always returns a 403 JSON response.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== UserRole::Student) {
            abort(403, 'This action requires a student account.');
        }

        return $next($request);
    }
}
