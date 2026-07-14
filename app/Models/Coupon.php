<?php

namespace App\Models;

use App\Core\Model;

final class Coupon extends Model
{
    protected static string $table = 'coupons';

    public static function findValidByCode(string $code): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM coupons
             WHERE code = :code
               AND is_active = 1
               AND (valid_from IS NULL OR valid_from <= NOW())
               AND (valid_to IS NULL OR valid_to >= NOW())
               AND (max_uses IS NULL OR used_count < max_uses)
             LIMIT 1'
        );
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function incrementUsage(int $id): void
    {
        $stmt = static::db()->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
