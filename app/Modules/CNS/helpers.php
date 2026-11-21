<?php

if (!function_exists('cns_can')) {
    function cns_can(string $menuSlug, string $action = 'view'): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if ($user->can("cns.{$menuSlug}.{$action}")) {
            return true;
        }

        $provider = app(\App\Services\Rbac\PermissionRegistry::class)->provider('cns');
        $menuId = $provider ? $provider->menuIdForSlug($menuSlug) : null;

        return $menuId ? $user->can("menu.{$menuId}.{$action}") : false;
    }
}
