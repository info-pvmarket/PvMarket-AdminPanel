<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Check if user can access admin panel
        if (!$user->canAccessAdmin()) {
            abort(403, 'You do not have access to the admin panel.');
        }

        // Check specific permission
        if (!$user->hasAdminPermission($permission)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
