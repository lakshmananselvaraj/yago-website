<?php

namespace App\Models;

use App\Core\Model;

final class InstructorBlockedDate extends Model
{
    protected static string $table = 'instructor_blocked_dates';

    public static function forInstructor(int $instructorId): array
    {
        $stmt = static::db()->prepare(
            'SELECT * FROM instructor_blocked_dates WHERE instructor_id = :id OR instructor_id IS NULL ORDER BY blocked_date ASC'
        );
        $stmt->execute(['id' => $instructorId]);

        return $stmt->fetchAll();
    }
}
