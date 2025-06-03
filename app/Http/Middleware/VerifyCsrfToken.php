<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login', // Bỏ qua kiểm tra CSRF cho login nếu là API
        'api/*', // Bỏ qua tất cả các route API
    ];
}
