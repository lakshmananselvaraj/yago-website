<?php

namespace App\Models;

use App\Core\Model;

final class Package extends Model
{
    protected static string $table = 'packages';

    public static function forServiceType(int $serviceTypeId): array
    {
        return self::where(['service_type_id' => $serviceTypeId, 'is_active' => 1]);
    }

    public static function featured(): array
    {
        return self::where(['is_active' => 1, 'is_featured' => 1], 'id ASC');
    }
}
