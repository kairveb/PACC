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

        $this->assertTrue(User::where('email', 'super-admin@coor.test')->exists());
        $this->assertTrue(User::where('email', 'hospital-admin@coor.test')->exists());

        $this->assertTrue(Auth::attempt([
            'email' => 'super-admin@coor.test',
            'password' => 'Password123!',
        ]));
    }
}
