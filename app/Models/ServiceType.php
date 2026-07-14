<?php

namespace App\Models;

use App\Core\Model;

final class ServiceType extends Model
{
    protected static string $table = 'service_types';

    public static function activeOrdered(): array
    {
        return self::where(['is_active' => 1], 'sort_order ASC');
    }
}
