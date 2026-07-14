<?php

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    protected static string $table = 'notifications';

    public static function create(int $userId, string $type, string $title, ?string $body = null, string $channel = 'in_app'): int
    {
        return self::insert([
            'user_id' => $userId,
            'type' => $type,
            'channel' => $channel,
            'title' => $title,
            'body' => $body,
        ]);
    }

    public static function recent(int $limit = 50): array
    {
        $db = static::db();
        $stmt = $db->prepare(
            'SELECT notifications.*, users.name AS user_name, users.email AS user_email
             FROM notifications
             LEFT JOIN users ON users.id = notifications.user_id
             ORDER BY notifications.id DESC
             LIMIT ' . max(1, $limit)
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function forUser(int $userId, int $limit = 50): array
    {
        return self::where(['user_id' => $userId], 'id DESC', $limit);
    }

    public static function unreadCountForUser(int $userId): int
    {
        return self::count(['user_id' => $userId, 'is_read' => 0]);
    }

    public static function markRead(int $id): void
    {
        self::update($id, ['is_read' => 1]);
    }
}
