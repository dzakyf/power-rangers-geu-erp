<?php

namespace App\Services\Rbac;

use App\Contracts\ModulePermissionProvider;

class PermissionRegistry
{
    /** @var array<string, ModulePermissionProvider> */
    private array $providers = [];

    public function register(ModulePermissionProvider $provider): void
    {
        $this->providers[$provider->module()] = $provider;
    }

    public function has(string $module): bool
    {
        return isset($this->providers[$module]);
    }

    public function provider(string $module): ?ModulePermissionProvider
    {
        return $this->providers[$module] ?? null;
    }

    /** @return array<string, ModulePermissionProvider> */
    public function all(): array
    {
        return $this->providers;
    }

    public function providerForMenu(string $menuId): ?ModulePermissionProvider
    {
        foreach ($this->providers as $provider) {
            if ($provider->ownsMenu($menuId)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Menu Modules accepted by the credential editor: core-owned modules
     * plus the modules enabled for this deployment.
     */
    public function menuModules(): array
    {
        $modules = (array) config('modules.core_menus', []);

        foreach ($this->providers as $provider) {
            $modules = array_merge($modules, $provider->menuModules());
        }

        return array_values(array_unique($modules));
    }

    /**
     * Merged route permission map from every enabled module.
     */
    public function routeRequirements(): array
    {
        $merged = [];

        foreach ($this->providers as $provider) {
            $merged = array_merge($merged, $provider->routeRequirements());
        }

        return $merged;
    }
}
