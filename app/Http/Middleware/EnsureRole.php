<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthenticated.');
        }

        // Super admin can access everything
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
