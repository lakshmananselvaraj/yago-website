<?php

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\PermissionMiddleware;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}

final class Middleware
{
    public static function resolve(string $name): MiddlewareInterface
    {
        return match (true) {
            $name === 'auth' => new AuthMiddleware(),
            $name === 'guest' => new GuestMiddleware(),
            $name === 'csrf' => new CsrfMiddleware(),
            str_starts_with($name, 'rate:') => new \App\Middleware\RateLimitMiddleware(substr($name, 5)),
            str_starts_with($name, 'role:') => new \App\Middleware\RoleMiddleware(substr($name, 5)),
            str_starts_with($name, 'permission:') => new PermissionMiddleware(substr($name, 11)),
            default => throw new \RuntimeException("Unknown middleware: {$name}"),
        };
    }
}
