<?php

namespace Tests\Feature\Auth;

use Database\Seeders\HimsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoUserCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_demo_users_use_documented_credentials(): void
    {
        $this->artisan('db:seed', ['--class' => HimsSeeder::class])->assertSuccessful();

        $superAdmin = \App\Models\User::where('email', 'super.admin@coor.test')->first();
        $hospitalAdmin = \App\Models\User::where('email', 'hospital.admin@coor.test')->first();
        $doctor = \App\Models\User::where('email', 'doctor@coor.test')->first();

        $this->assertNotNull($superAdmin);
        $this->assertNotNull($hospitalAdmin);
        $this->assertNotNull($doctor);
        $this->assertTrue(Hash::check('Password123!', $superAdmin->password));
        $this->assertTrue(Hash::check('Password123!', $hospitalAdmin->password));
        $this->assertTrue(Hash::check('Password123!', $doctor->password));
    }
}
