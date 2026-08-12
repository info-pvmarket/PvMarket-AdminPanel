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
        try {
            return $this->authorize($request, $next, $permission);
        } catch (\Throwable $exception) {
            $user = $request->user();
            $canViewDiagnostic = $request->is('user/listings')
                && strtolower((string) $user?->email) === 'info@pv.market';

            if (! $canViewDiagnostic) {
                throw $exception;
            }

            $message = str_replace(base_path(), '[app]', $exception->getMessage());
            $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $message) ?? '';
            $message = preg_replace('/\b[a-f0-9]{24}\b/i', '[object-id]', $message) ?? '';
            $message = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $message) ?? '';

            return response("Server Error\n".class_basename($exception)."\n{$message}", 500);
        }
    }

    private function authorize(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        // Check if user can access admin panel
        if (! $user->canAccessAdmin()) {
            abort(403, 'You do not have access to the admin panel.');
        }

        // Check specific permission
        if (! $user->hasAdminPermission($permission)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
