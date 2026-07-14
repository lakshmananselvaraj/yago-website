<?php

namespace App\Models;

use App\Core\Model;

final class SessionResource extends Model
{
    protected static string $table = 'session_resources';

    public static function forBooking(int $bookingId): array
    {
        return self::where(['booking_id' => $bookingId], 'created_at DESC');
    }
}
