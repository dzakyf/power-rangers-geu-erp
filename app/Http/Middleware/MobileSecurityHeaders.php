<?php

namespace App\Http\Middleware;

use Closure;

class MobileSecurityHeaders
{
    /**
     * Apply defensive security headers for mobile web and API responses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'SAMEORIGIN');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->header('X-XSS-Protection', '1; mode=block');

            if ($request->is('api/*') || $request->is('mobile/login') || $request->is('mobile/sessions*')) {
                $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->header('Pragma', 'no-cache');
            }
        }

        return $response;
    }
}
