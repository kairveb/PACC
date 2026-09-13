<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Blade;
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
        $this->registerRoleBladeDirective();
        $this->registerPermissionGates();
    }

    protected function registerRoleBladeDirective(): void
    {
        Blade::directive('role', function ($expression) {
            return "<?php if (auth()->check() && auth()->user()->hasRole({$expression})): ?>";
        });

        Blade::directive('endrole', function () {
            return '<?php endif; ?>';
        });
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

        Gate::define('portal-dashboard', fn ($user) => $user->hasRole('patient'));
        Gate::define('view-own-medical-history', fn ($user) => $user->hasRole('patient'));
        Gate::define('view-own-appointments', fn ($user) => $user->hasRole('patient'));
        Gate::define('view-own-telehealth', fn ($user) => $user->hasRole('patient'));

        // Super admin bypass and dynamic permission checks for seeded permissions.
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            if (in_array($ability, ['portal-dashboard', 'view-own-medical-history', 'view-own-appointments', 'view-own-telehealth'], true)) {
                return $user->hasRole('patient');
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
