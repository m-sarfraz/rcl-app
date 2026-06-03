<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect('/')->with('error', 'Your account has been deactivated.');
        }

        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        if (!empty($roles) && !in_array($user->role?->name, $roles) && !$user->isSuperAdmin()) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
