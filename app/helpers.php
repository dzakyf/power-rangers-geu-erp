<?php

if (!function_exists('can_menu')) {
    function can_menu(string $menuId, string $action = 'view'): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->can("menu.{$menuId}.{$action}");
    }
}
