<?php

namespace App\Models;

use App\Core\Model;

final class SessionFeedback extends Model
{
    protected static string $table = 'session_feedback';

    public static function forBooking(int $bookingId): ?array
    {
        return self::whereFirst(['booking_id' => $bookingId]);
    }

    public static function forInstructor(int $instructorId): array
    {
        return self::where(['instructor_id' => $instructorId], 'created_at DESC');
    }

    public static function forClient(int $clientId): array
    {
        return self::where(['client_id' => $clientId], 'created_at DESC');
    }
}
