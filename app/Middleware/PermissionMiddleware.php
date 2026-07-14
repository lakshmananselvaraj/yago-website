<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private string $permission)
    {
    }

    public function handle(Request $request): void
    {
        if (Auth::can($this->permission)) {
            return;
        }

        if (str_starts_with($request->path(), '/api/')) {
            Response::json(['success' => false, 'message' => 'You do not have permission to access this resource.'], 403);
        }

        http_response_code(403);
        echo View::render('errors/403', [], 'main');
        exit;
    }
}
