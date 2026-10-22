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
        // Block unauthenticated requests before any route/view code executes
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ambil payload / data request (baik dari POST, PUT, atau Query Parameters)
        // json_encode digunakan agar data array/object bisa disimpan rapi dalam bentuk string JSON
        $payload = json_encode($request->all());

        // Jika Anda ingin menyaring data sensitif (seperti password) agar tidak ikut tercatat:
        // $payload = json_encode($request->except(['password', 'password_confirmation']));

        // Execute the request only for authenticated users, then track it
        $response = $next($request);

        // Capture user information if they are authenticated
        $userEmail = Auth::check() ? (Auth::user()->email != '' ? Auth::user()->email : 'N/A') : 'N/A';
        
        if (!Str::contains($request->fullUrl(), ['notification-getall','xml'])) {
            // Structure the log payload
            UserLog::create([
                'username'   => Auth::check() ? Auth::user()->username : 'guest',
                'name'       => Auth::check() ? Auth::user()->Name : 'Guest',
                'email'      => $userEmail,
                'ip_address' => $request->ip(),
                'method'     => $request->method(),
                'url'        => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'payload'    => $payload,
            ]);
        }

        return $response;
    }
}