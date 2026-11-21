<?php

namespace App\Modules\HCGA\Rbac;

use App\Contracts\ModulePermissionProvider;

class HcgaPermissionProvider implements ModulePermissionProvider
{
    public function module(): string
    {
        return 'hcga';
    }

    public function prefix(): string
    {
        return 'menu';
    }

    public function ownsMenu(string $menuId): bool
    {
        return false;
    }

    public function permissionName(string $menuId, string $action): string
    {
        return "menu.{$menuId}.{$action}";
    }

    public function slugFor(string $menuId): ?string
    {
        return null;
    }

    public function menuIdForSlug(string $slug): ?string
    {
        return null;
    }

    public function allMenuSlugs(): array
    {
        return [];
    }

    public function menuModules(): array
    {
        return ['HRD'];
    }

    public function routeRequirements(): array
    {
        return [];
    }
}
