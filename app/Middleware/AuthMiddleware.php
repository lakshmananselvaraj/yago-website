<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        if (str_starts_with($request->path(), '/api/')) {
            Response::json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        Response::redirect('/login?redirect=' . urlencode($request->fullPath()));
    }
}
