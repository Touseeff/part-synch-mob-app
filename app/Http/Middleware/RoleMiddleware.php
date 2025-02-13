<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roleId)
    {
        // Check if the authenticated user has the correct role_id
        if ($request->user()->role_id != $roleId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
