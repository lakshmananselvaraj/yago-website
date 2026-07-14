<?php

namespace App\Models;

use App\Core\Model;

final class Favorite extends Model
{
    protected static string $table = 'favorites';

    public static function isFavorited(?int $clientId, int $instructorId): bool
    {
        if ($clientId === null) {
            return false;
        }

        return self::whereFirst(['client_id' => $clientId, 'instructor_id' => $instructorId]) !== null;
    }

    public static function toggle(int $clientId, int $instructorId): bool
    {
        $existing = self::whereFirst(['client_id' => $clientId, 'instructor_id' => $instructorId]);

        if ($existing) {
            self::delete($existing['id']);

            return false;
        }

        self::insert(['client_id' => $clientId, 'instructor_id' => $instructorId]);

        return true;
    }

    public static function forClient(int $clientId): array
    {
        $stmt = static::db()->prepare(
            'SELECT f.*, i.id AS instructor_id, i.headline, i.avatar_path, i.rating_avg, i.rating_count, u.name
             FROM favorites f
             INNER JOIN instructors i ON i.id = f.instructor_id
             INNER JOIN users u ON u.id = i.user_id
             WHERE f.client_id = :client_id
             ORDER BY f.created_at DESC'
        );
        $stmt->execute(['client_id' => $clientId]);

        return $stmt->fetchAll();
    }
}
