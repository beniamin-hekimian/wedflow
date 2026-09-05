<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->role === 'admin') {
            return $next($request);
        }

        return redirect()
            ->route('home')
            ->with('error', 'You are not authorized to access the admin area.');
    }
}