<?php

namespace App\Models;

use App\Core\Model;

final class InstructorService extends Model
{
    protected static string $table = 'instructor_services';

    public static function effectivePrice(int $instructorId, int $packageId): ?float
    {
        $stmt = static::db()->prepare(
            'SELECT instructor_services.price_override, packages.price
             FROM instructor_services
             INNER JOIN packages ON packages.id = instructor_services.package_id
             WHERE instructor_services.instructor_id = :instructor_id
               AND instructor_services.package_id = :package_id
               AND instructor_services.is_active = 1
             LIMIT 1'
        );
        $stmt->execute(['instructor_id' => $instructorId, 'package_id' => $packageId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $row['price_override'] !== null ? (float) $row['price_override'] : (float) $row['price'];
    }
}
