<?php

namespace App\Support;

/**
 * Reads the module registry (config/modules.php) so the application can
 * compose itself from a subset of modules per deployment (MODULES env).
 */
class Modules
{
    /**
     * The enabled modules, deduplicated and sorted into the registry's
     * canonical load order (config/modules.php `routes` map) so a mis-ordered
     * MODULES value can never load a consumer before its producer.
     */
    public static function enabled(): array
    {
        $enabled = array_values(array_unique(array_filter((array) config('modules.enabled', []))));
        $order = array_keys((array) config('modules.routes', []));

        usort($enabled, function ($a, $b) use ($order) {
            $ia = array_search($a, $order, true);
            $ib = array_search($b, $order, true);

            return ($ia === false ? PHP_INT_MAX : $ia) <=> ($ib === false ? PHP_INT_MAX : $ib);
        });

        return array_values($enabled);
    }

    public static function isEnabled(string $module): bool
    {
        return in_array($module, self::enabled(), true);
    }

    /**
     * Service provider class for a module, or null when it has none.
     */
    public static function provider(string $module): ?string
    {
        return config("modules.providers.{$module}");
    }

    /**
     * Provider classes for every enabled module that declares one.
     */
    public static function enabledProviders(): array
    {
        $providers = [];

        foreach (self::enabled() as $module) {
            $provider = self::provider($module);

            if ($provider) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    /**
     * Absolute path of a module's route file, or null when undefined.
     */
    public static function routeFile(string $module): ?string
    {
        $file = config("modules.routes.{$module}");

        return $file ? base_path("routes/modules/{$file}.php") : null;
    }
}
