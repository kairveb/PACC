<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HimsSeederCreatesLoginUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_creates_default_login_accounts(): void
    {
        Artisan::call('db:seed', ['--class' => HimsSeeder::class]);

        $superAdmin = User::where('email', 'super-admin@coor.test')->first();
        $patient = User::where('email', 'patient@coor.test')->first();

        $this->assertNotNull($superAdmin);
        $this->assertNotNull($patient);
        $this->assertTrue($superAdmin->hasVerifiedEmail());
        $this->assertTrue($patient->hasVerifiedEmail());

        $this->assertTrue(Auth::attempt([
            'email' => 'super-admin@coor.test',
            'password' => 'Password123!',
        ]));

        Auth::logout();

        $this->assertTrue(Auth::attempt([
            'email' => 'patient@coor.test',
            'password' => 'Password123!',
        ]));
    }
}
