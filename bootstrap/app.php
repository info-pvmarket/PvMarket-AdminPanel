<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', env('TRUSTED_PROXIES', '127.0.0.1'))
        )));

        // Caddy normalizes Cloudflare's forwarded headers before proxying to
        // this private container address.
        $middleware->trustProxies(
            at: $trustedProxies,
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX
        );

        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo('/admin/dashboard');

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'admin.permission' => \App\Http\Middleware\CheckAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $exception): void {
            $request = request();
            if (! $request->is('user/listings')) {
                return;
            }

            $message = str_replace(base_path(), '[app]', $exception->getMessage());
            $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $message) ?? '';
            $message = preg_replace('/\b[a-f0-9]{24}\b/i', '[object-id]', $message) ?? '';
            $message = preg_replace('#(https?|mongodb(?:\+srv)?|redis|smtp)://\S+#i', '[connection]', $message) ?? '';
            $message = preg_replace('/(password|token|secret|authorization)[=:]\S+/i', '$1=[redacted]', $message) ?? '';
            $line = class_basename($exception).' '.substr($message, 0, 1000);

            file_put_contents(
                storage_path('logs/listings-diagnostic.log'),
                $line.PHP_EOL,
                LOCK_EX,
            );
        });
    })->create();
