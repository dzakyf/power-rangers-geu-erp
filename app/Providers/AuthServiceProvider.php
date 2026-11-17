<?php

namespace App\Providers;

use App\Services\Approval\ApprovalAuthorizationService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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
    public function register()
    {
        Gate::before(function ($user, $ability) {
            if ($user->username === 'admin'
                || ($user->Priv_ID ?? '') === 'ADM'
                || (method_exists($user, 'hasRole') && rescue(function () use ($user) { return $user->hasRole('ADM') || $user->hasRole('super-admin'); }, false))) {
                return true;
            }

            return null;
        });
    }

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('cns.master_user.view', fn ($user) => $user->can('cns.user_management.view'));
        Gate::define('cns.master_user.create', fn ($user) => $user->can('cns.user_management.create'));
        Gate::define('cns.master_user.edit', fn ($user) => $user->can('cns.user_management.edit'));
        Gate::define('cns.master_user.delete', fn ($user) => $user->can('cns.user_management.delete'));

        \Auth::provider('active_eloquent', function ($app, array $config) {
            return new \App\Auth\ActiveUserProvider($app['hash'], $config['model']);
        });

        // Wildcard admin may use every approval.* ability (claim release,
        // self-approval exception, etc.). Non-admin users are untouched.
        Gate::before(function ($user, $ability) {
            if (Str::startsWith((string) $ability, 'approval.')
                && app(ApprovalAuthorizationService::class)->isWildcardAdmin($user)) {
                return true;
            }

            return null;
        });
    }
}
