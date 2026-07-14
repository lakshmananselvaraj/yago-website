<?php

namespace App\Models;

use App\Core\Model;

final class InstructorGalleryImage extends Model
{
    protected static string $table = 'instructor_gallery_images';

    public static function forInstructor(int $instructorId): array
    {
        return self::where(['instructor_id' => $instructorId], 'created_at DESC');
    }
}
