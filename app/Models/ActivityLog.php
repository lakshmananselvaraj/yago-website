<?php

namespace App\Models;

use App\Core\Model;

final class ActivityLog extends Model
{
    protected static string $table = 'activity_logs';

    public static function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        self::insert([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            'meta' => !empty($meta) ? json_encode($meta) : null,
        ]);
    }

    public static function recent(int $limit = 50): array
    {
        $db = static::db();
        $stmt = $db->prepare(
            'SELECT activity_logs.*, users.name AS user_name
             FROM activity_logs
             LEFT JOIN users ON users.id = activity_logs.user_id
             ORDER BY activity_logs.id DESC
             LIMIT ' . max(1, $limit)
        );
        $stmt->execute();

        return array_map(static function (array $row): array {
            $row['meta'] = $row['meta'] !== null ? json_decode($row['meta'], true) : null;
            return $row;
        }, $stmt->fetchAll());
    }
}
