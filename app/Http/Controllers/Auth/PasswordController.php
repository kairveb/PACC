<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * @throws ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
                'confirmed',
            ],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.letters' => 'Password must include at least one letter.',
            'password.mixed_case' => 'Password must include both upper and lower case letters.',
            'password.numbers' => 'Password must include at least one number.',
            'password.symbols' => 'Password must include at least one symbol.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
