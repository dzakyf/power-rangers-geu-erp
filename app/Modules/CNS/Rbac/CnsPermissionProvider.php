<?php

namespace App\Modules\CNS\Rbac;

use App\Contracts\ModulePermissionProvider;
use App\Services\CNS\CnsPermissionService;

class CnsPermissionProvider implements ModulePermissionProvider
{
    public function module(): string
    {
        return 'cns';
    }

    public function prefix(): string
    {
        return 'cns';
    }

    public function ownsMenu(string $menuId): bool
    {
        return CnsPermissionService::isCnsMenu($menuId);
    }

    public function permissionName(string $menuId, string $action): string
    {
        return CnsPermissionService::permissionName($menuId, $action);
    }

    public function slugFor(string $menuId): ?string
    {
        return CnsPermissionService::getSlug($menuId);
    }

    public function menuIdForSlug(string $slug): ?string
    {
        return CnsPermissionService::getMenuId($slug);
    }

    public function allMenuSlugs(): array
    {
        return CnsPermissionService::allMenuSlugs();
    }

    public function menuModules(): array
    {
        return ['CNS'];
    }

    public function routeRequirements(): array
    {
        return [];
    }
}
