<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMember
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'member') {
            if (session('role') === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('login')->withErrors(['login' => 'Silakan login sebagai member terlebih dahulu.']);
        }

        return $next($request);
    }
}
