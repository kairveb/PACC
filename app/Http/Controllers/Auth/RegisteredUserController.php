<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\PatientService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.letters' => 'Password must include at least one letter.',
            'password.mixed_case' => 'Password must include both upper and lower case letters.',
            'password.numbers' => 'Password must include at least one number.',
            'password.symbols' => 'Password must include at least one symbol.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $patientRole = Role::where('name', 'patient')->first();
        if ($patientRole) {
            $user->roles()->syncWithoutDetaching([$patientRole->id]);
        }

        $nameParts = preg_split('/\s+/', trim($request->name));
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[count($nameParts) - 1] ?? '';

        $user->patient()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'mrn' => app(PatientService::class)->generateMpn(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => now()->subYears(18)->toDateString(),
                'sex' => 'Not specified',
                'email' => $user->email,
                'lookup_code' => strtoupper(Str::random(8)),
                'pre_registration_status' => 'not_started',
                'verified' => false,
            ]
        );

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
