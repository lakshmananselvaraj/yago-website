<?php

namespace App\Middleware;

use App\Core\MiddlewareInterface;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;

final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(private string $bucket)
    {
    }

    public function handle(Request $request): void
    {
        $config = (require dirname(__DIR__) . '/Config/app.php')['rate_limits'][$this->bucket]
            ?? ['max' => 10, 'decay' => 60];

        $key = $this->bucket . ':' . $request->ip();

        if (!RateLimiter::attempt($key, $config['max'], $config['decay'])) {
            Response::json([
                'success' => false,
                'message' => 'Too many attempts. Please wait a moment and try again.',
            ], 429);
        }
    }
}
