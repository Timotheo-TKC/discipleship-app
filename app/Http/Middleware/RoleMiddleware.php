<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|array  $roles
     */
    public function handle(Request $request, Closure $next, string|array $roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Convert single role to array
        if (is_string($roles)) {
            $roles = [$roles];
        }

        // Check if user has any of the required roles
        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Access denied. You do not have the required role.');
        }

        return $next($request);
    }
}
