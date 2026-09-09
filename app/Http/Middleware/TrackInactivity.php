<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackInactivity
{
    public const IDLE_TIMEOUT = 900; // 15 minutes in seconds

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('inactivity.enabled', true)) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $lastSeen = $user->last_activity_at;
        $timeoutSeconds = (int) config('inactivity.timeout', self::IDLE_TIMEOUT);
        $threshold = now()->subSeconds($timeoutSeconds);

        if ($lastSeen !== null && $lastSeen->lt($threshold)) {
            if (Auth::guard('web')->check() && $request->hasSession()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You have been logged out due to inactivity.',
                ], 401);
            }

            return redirect()->route('login')->with('status', 'You have been logged out due to inactivity.');
        }

        $user->forceFill(['last_activity_at' => now()])->save();

        return $next($request);
    }
}

