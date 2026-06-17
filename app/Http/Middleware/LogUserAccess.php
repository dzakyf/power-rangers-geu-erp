<?php

namespace App\Http\Middleware;

use App\UserLog;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Execute the request first so we track it as it happens
        $response = $next($request);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Capture user information if they are authenticated
        // $userId = Auth::check() ? Auth::id() : 'Guest';
        $userEmail = Auth::check() ? Auth::user()->email : 'N/A';
        if (!Str::contains($request->fullUrl(), ['notification-getall','xml'])) {
            // Structure the log payload
            UserLog::create([
                'username'    => Auth::check() ? Auth::user()->username : 'guest',
                'name'        => Auth::check() ? Auth::user()->Name : 'Guest',
                'email'      => $userEmail,
                'ip_address' => $request->ip(),
                'method'     => $request->method(),
                'url'        => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);
        }


        return $response;
    }
}
