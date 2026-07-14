<?php

namespace App\Models;

use App\Core\Model;

final class ClientProfile extends Model
{
    protected static string $table = 'client_profiles';

    public static function findByUserId(int $userId): ?array
    {
        return self::whereFirst(['user_id' => $userId]);
    }

    public static function upsertForUser(int $userId, array $data): void
    {
        if (isset($data['preferences']) && is_array($data['preferences'])) {
            $data['preferences'] = json_encode($data['preferences']);
        }

        $existing = self::findByUserId($userId);

        if ($existing) {
            self::update($existing['id'], $data);
            return;
        }

        $data['user_id'] = $userId;
        self::insert($data);
    }

    public static function hydrate(array $row): array
    {
        $row['preferences'] = $row['preferences'] !== null ? json_decode($row['preferences'], true) : null;

        return $row;
    }
}
