<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $token = $request->input('csrf_token') ?? $request->header('X-CSRF-Token');

        if (!Csrf::verify($token)) {
            Response::json(['success' => false, 'message' => 'Invalid or expired security token. Please refresh and try again.'], 419);
        }
    }
}
