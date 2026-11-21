<?php

namespace App\Http\Middleware;

use App\Services\Rbac\PermissionRegistry;
use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureMenuPermission
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Let the existing auth middleware handle guests/redirects.
        if (!$user) {
            return $next($request);
        }

        $route = $request->route();
        if (!$route) {
            return $next($request);
        }

        $method = strtoupper($request->method());
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $key = $method . ' ' . ltrim($route->uri(), '/');
        $requirements = app(PermissionRegistry::class)->routeRequirements()[$key] ?? null;

        if (!$requirements) {
            return $next($request);
        }

        // ADM and super-admin keep a safety bypass, consistent with the
        // credential role editor.
        if ($user->hasRole('ADM') || $user->hasRole('super-admin')) {
            return $next($request);
        }

        foreach ($this->normalizeRequirements($requirements) as [$menuId, $action]) {
            if (can_menu($menuId, $action)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        abort(403, 'You do not have permission to perform this action.');
    }

    private function normalizeRequirements($requirements): array
    {
        if (isset($requirements[0]) && is_string($requirements[0])) {
            return [$requirements];
        }

        return $requirements;
    }
}
