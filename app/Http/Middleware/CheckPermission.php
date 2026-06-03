<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check() || !auth()->user()->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            abort(403, 'You do not have permission: ' . $permission);
        }

        return $next($request);
    }
}
