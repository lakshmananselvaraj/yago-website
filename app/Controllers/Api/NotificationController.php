<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $this->success([
            'notifications' => Notification::forUser(Auth::id(), 8),
            'unread_count' => Notification::unreadCountForUser(Auth::id()),
        ]);
    }
}
