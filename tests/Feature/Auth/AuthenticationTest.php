<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_is_not_rejected_when_last_activity_timestamp_is_stale(): void
    {
        $user = User::factory()->create([
            'last_activity_at' => now()->subHours(2),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_successful_login_updates_last_activity_at_immediately(): void
    {
        $user = User::factory()->create([
            'last_activity_at' => now()->subHours(2),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();

        $this->assertNotNull($user->last_activity_at);
        $this->assertTrue($user->last_activity_at->diffInSeconds(now()) < 10);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_stale_inactivity_logs_user_out_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'last_activity_at' => now()->subSeconds(901),
        ]);

        $response = $this->actingAs($user, 'web')->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $this->assertSame('You have been logged out due to inactivity.', session('status'));
    }

    public function test_recent_activity_keeps_user_logged_in_and_updates_last_activity_timestamp(): void
    {
        $user = User::factory()->create([
            'last_activity_at' => now()->subMinutes(2),
        ]);

        $response = $this->actingAs($user, 'web')->get('/dashboard');

        $response->assertOk();
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertTrue($user->last_activity_at->diffInSeconds(now()) < 10);
    }

    public function test_ajax_like_request_updates_last_activity_timestamp_within_timeout_window(): void
    {
        $user = User::factory()->create([
            'last_activity_at' => now()->subMinutes(2),
        ]);

        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->actingAs($user, 'web')
            ->get('/dashboard');

        $user->refresh();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->last_activity_at->diffInSeconds(now()) < 10);
    }

    public function test_inactivity_timeout_applies_across_multiple_roles(): void
    {
        foreach (['super-admin', 'doctor', 'patient'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName], ['label' => ucfirst(str_replace('-', ' ', $roleName))]);
            $user = User::factory()->create([
                'last_activity_at' => now()->subSeconds(901),
            ]);
            $user->roles()->attach($role->id);

            $response = $this->actingAs($user, 'web')->get('/dashboard');

            $this->assertGuest();
            $response->assertRedirect('/login');
        }
    }

    public function test_authenticated_pages_are_marked_no_cache_and_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();

        $cacheControl = $response->headers->get('Cache-Control', '');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
