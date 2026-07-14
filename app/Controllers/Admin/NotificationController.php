<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;

final class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/notifications', [
            'notifications' => Notification::recent(100),
            'users' => User::all('name ASC'),
        ], 'dashboard');
    }

    public function store(Request $request): void
    {
        $target = (string) $request->input('target', 'user');
        $title = trim((string) $request->input('title', ''));
        $body = trim((string) $request->input('body', '')) ?: null;

        if ($title === '' || mb_strlen($title) > 200) {
            $this->fail('Please enter a title (max 200 characters).', 422);
        }

        if ($target === 'all_clients' || $target === 'all_trainers') {
            $role = $target === 'all_clients' ? 'client' : 'instructor';
            $recipients = User::where(['role' => $role, 'status' => 'active']);

            foreach ($recipients as $recipient) {
                Notification::create((int) $recipient['id'], 'admin_broadcast', $title, $body);
            }

            ActivityLog::log(Auth::id(), 'notification_broadcast', 'notification', null, [
                'target' => $target,
                'recipient_count' => count($recipients),
                'title' => $title,
            ]);

            $this->success(['recipient_count' => count($recipients)], sprintf('Sent to %d recipient(s).', count($recipients)));
        }

        $userId = (int) $request->input('user_id', 0);

        if ($userId <= 0) {
            $this->fail('Please choose a recipient.', 422);
        }

        $id = Notification::create($userId, 'admin_message', $title, $body);

        ActivityLog::log(Auth::id(), 'notification_sent', 'notification', $id, [
            'to_user_id' => $userId,
            'title' => $title,
        ]);

        $this->success(['id' => $id], 'Notification sent.');
    }
}
