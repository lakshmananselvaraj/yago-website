<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Notification;

final class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $notifications = Notification::forUser(Auth::id(), 100);

        foreach ($notifications as $notification) {
            if (!$notification['is_read']) {
                Notification::markRead((int) $notification['id']);
            }
        }

        $this->view('trainer/notifications', ['notifications' => $notifications], 'dashboard');
    }
}
