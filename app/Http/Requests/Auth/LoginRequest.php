<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

/**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $user = \App\Models\User::where('email', $credentials['email'] ?? null)->first();
        Log::debug('LOGIN_DEBUG pre-attempt', [
            'email' => $credentials['email'] ?? null,
            'password_present' => ! empty($credentials['password']),
            'password_length' => strlen((string) ($credentials['password'] ?? '')),
            'user_exists' => (bool) $user,
            'hash_check' => $user ? Hash::check((string) ($credentials['password'] ?? ''), $user->password) : false,
        ]);

        $attempt = Auth::attempt($credentials, $this->boolean('remember'));
        Log::debug('LOGIN_DEBUG post-attempt', ['attempt' => $attempt]);

        if (! $attempt) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // If the authenticated user has MFA enabled we do NOT finalize the
        // session here. Instead we stash their identity in the session and
        // require a TOTP code on the challenge screen before login completes.
        $user = Auth::user();
        if ($user && $user->hasMfaEnabled()) {
            $this->session()->put('mfa', [
                'id' => $user->getKey(),
                'secret' => $user->mfa_secret,
                'verified' => true,
                'started_at' => now()->timestamp,
            ]);
        }
    }

    /**
     * Whether the authenticated user must complete an MFA challenge.
     */
    public function mfaRequired(): bool
    {
        return $this->session()->has('mfa');
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
