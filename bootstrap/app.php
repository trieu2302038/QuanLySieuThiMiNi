<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VerifyCsrfToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // ✅ Thêm dòng này để load API routes
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đảm bảo bật session cho API nếu cần thiết
        $middleware->group('api', [
            \Illuminate\Session\Middleware\StartSession::class, // ✅ Quan trọng: Bật session trong API
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ]);
        
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'role' => RoleMiddleware::class, // Gộp vào đây
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý exceptions tại đây (nếu cần)
    })
    ->create();
