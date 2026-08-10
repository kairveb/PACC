<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPermissionGates();
    }

    /**
     * Register a Gate for every permission record so that
     * Gate::allows('view-patients') etc. resolve through the user's roles.
     */
    protected function registerPermissionGates(): void
    {
        try {
            foreach (Permission::all() as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->isSuperAdmin() || $user->hasPermission($permission->name);
                });
            }
        } catch (\Throwable $e) {
            // Database may not be migrated yet (e.g., during install). Skip gracefully.
        }

        // Super admin bypass and dynamic permission checks for seeded permissions.
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            try {
                if (Permission::where('name', $ability)->exists()) {
                    return $user->hasPermission($ability);
                }
            } catch (\Throwable $e) {
                // Database not ready yet while bootstrapping.
            }

            return null;
        });
    }
}
