<?php

namespace App\Models;

use App\Core\Model;

final class InstructorCertificateFile extends Model
{
    protected static string $table = 'instructor_certificate_files';

    public static function forInstructor(int $instructorId): array
    {
        return self::where(['instructor_id' => $instructorId], 'created_at DESC');
    }
}
