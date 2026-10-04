<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');

        if (str_contains($host, ':8092')) {
            config(['session.cookie' => 'kurmigo-hris-ess-session']);
        }
    }

    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        try {
            if (Schema::hasTable('permissions')) {
                $permissions = Permission::all();
                foreach ($permissions as $permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                }
            }
        } catch (\Exception $e) {
            //
        }
    }
}
