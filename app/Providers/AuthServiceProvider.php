<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('super_admin', function ($user) {
            return $user->hasRole(Role::ROLE_SUPER_ADMIN);
        });

        Gate::define('admin', function ($user) {
            return $user->hasAnyRoles([Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN]);
        });

        Gate::define('moderator', function ($user) {
            return $user->hasAnyRoles([Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN, Role::ROLE_MODERATOR]);
        });

        Gate::define('editor', function ($user) {
            return $user->hasAnyRoles([Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN, Role::ROLE_MODERATOR, Role::ROLE_EDITOR]);
        });

        Gate::define('authenticated', function ($user) {
            return $user->hasAnyRoles([Role::ROLE_SUPER_ADMIN, Role::ROLE_ADMIN, Role::ROLE_MODERATOR, Role::ROLE_AUTHENTICATED]);
        });
    }
}
