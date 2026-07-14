<?php

namespace App\Models;

use App\Core\Model;

final class InstructorAvailability extends Model
{
    protected static string $table = 'instructor_availability';

    public static function forInstructor(int $instructorId): array
    {
        return self::where(['instructor_id' => $instructorId]);
    }
}
