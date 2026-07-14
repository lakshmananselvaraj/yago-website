<?php

namespace App\Models;

use App\Core\Model;

final class GalleryImage extends Model
{
    protected static string $table = 'gallery_images';

    /** Public gallery — active photos only. */
    public static function allOrdered(): array
    {
        return self::where(['is_active' => 1], 'sort_order ASC, id ASC');
    }

    /** Admin management view — every photo, active or hidden. */
    public static function adminAllOrdered(): array
    {
        return self::all('sort_order ASC, id ASC');
    }
}
