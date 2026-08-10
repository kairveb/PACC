<?php

namespace App\Http\Controllers;

use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * MfaController
 *
 * Handles two-factor (MFA) enrollment and management from the user's
 * profile page. It works hand-in-hand with the login challenge flow:
 *   - A user who has MFA enabled is sent a code after entering their password.
 *   - Here they can generate a secret, verify it once, and enable/disable MFA.
 */
class MfaController extends Controller
{
    public function __construct(protected MfaService $mfa)
    {
    }

    /**
     * Show the MFA setup page.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Provision a pending secret if the user has not finished setup.
        $pendingSecret = session('mfa_pending_secret');

        if (!$pendingSecret && !$user->hasMfaEnabled()) {
            $pendingSecret = $this->mfa->generateSecret();
            session(['mfa_pending_secret' => $pendingSecret]);
        }

        return view('profile.mfa', [
            'secret' => $pendingSecret,
            'qrData' => $pendingSecret
                ? $this->mfa->provisioningUri($pendingSecret, $user->email)
                : null,
        ]);
    }

    /**
     * Verify the pending secret with a code and enable MFA.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = session('mfa_pending_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expired. Please generate a new QR code.']);
        }

        if (!$this->mfa->verifyCode($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'The code you entered is invalid. Please try again.']);
        }

        $user->forceFill([
            'mfa_secret' => $secret,
            'mfa_enabled' => true,
        ])->save();

        $request->session()->forget('mfa_pending_secret');

        return redirect()->route('profile.edit')->with('status', 'mfa-enabled');
    }

    /**
     * Disable MFA after confirming the current code.
     */
    public function disable(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$user->hasMfaEnabled()) {
            return back()->withErrors(['code' => 'Two-factor authentication is not enabled.']);
        }

        if (!$this->mfa->verifyCode($user->mfa_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'The code you entered is invalid. Please try again.']);
        }

        $user->forceFill([
            'mfa_secret' => null,
            'mfa_enabled' => false,
        ])->save();

        return redirect()->route('profile.edit')->with('status', 'mfa-disabled');
    }
}
