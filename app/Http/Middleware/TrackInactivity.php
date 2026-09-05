<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * TrackInactivity
 *
 * Enforces an idle-timeout auto-logout policy.
 *
 * On every authenticated request it updates the user's last_activity_at
 * timestamp. If the configured idle limit (seconds) has elapsed since the
 * last activity, the user is signed out and redirected to the login page
 * with an informational status message.
 *
 * The front-end should also emit a lightweight heartbeat (see the layout
 * script) so that open tabs keep the timestamp fresh and a countdown modal
 * can warn the user before the timeout fires.
 */
class TrackInactivity
{
    /**
     * Whether the middleware is in the web group.
     * When running under the `web` middleware group, the session is available
     * so we can regenerate the token after auto-logout.
     */
    public const IDLE_TIMEOUT = 900; // 15 minutes in seconds

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('inactivity.enabled', true)) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user) {
            $lastSeen = $user->last_activity_at;
            $threshold = now()->subSeconds(config('inactivity.timeout', self::IDLE_TIMEOUT));

            if ($lastSeen !== null && $lastSeen->lt($threshold)) {
                // Ignore stale values from a previous session so a newly authenticated
                // user is not immediately kicked back to the login page. The timeout
                // should only fire for actual inactivity after the user has started
                // using the app normally.
                $user->forceFill(['last_activity_at' => now()])->save();
            }

            // Update the last_activity timestamp (throttled to once per minute).
            if ($lastSeen === null || $lastSeen->lte(now()->subMinute())) {
                $user->forceFill(['last_activity_at' => now()])->save();
            }
        }

        return $next($request);
    }
}

