<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'admin') {
            if (session('role') === 'member') {
                return redirect()->route('member.dashboard');
            }

            return redirect()->route('login')->withErrors(['login' => 'Silakan login sebagai admin terlebih dahulu.']);
        }

        return $next($request);
    }
}
