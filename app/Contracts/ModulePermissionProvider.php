<?php

namespace App\Contracts;

interface ModulePermissionProvider
{
    /**
     * Module key, e.g. 'cns', 'bp', 'finance', 'hcga'.
     */
    public function module(): string;

    /**
     * Permission prefix used in permission names, e.g. 'cns' or 'menu'.
     */
    public function prefix(): string;

    /**
     * True when this provider owns the given Menu_ID.
     */
    public function ownsMenu(string $menuId): bool;

    /**
     * Module permission name for a menu action, e.g. 'cns.folder_cns.view'.
     */
    public function permissionName(string $menuId, string $action): string;

    /**
     * Slug for a Menu_ID, or null when not owned by this provider.
     */
    public function slugFor(string $menuId): ?string;

    /**
     * Menu_ID for a slug, or null when not owned by this provider.
     */
    public function menuIdForSlug(string $slug): ?string;

    /**
     * All Menu_ID => slug pairs owned by the module.
     */
    public function allMenuSlugs(): array;

    /**
     * menu_lists.Modules values owned by the module, e.g. ['MITRA'].
     */
    public function menuModules(): array;

    /**
     * Route URI => [menu id, action] (or a list of alternatives) map.
     */
    public function routeRequirements(): array;
}
