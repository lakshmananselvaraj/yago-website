<?php

namespace App\Models;

use App\Core\Model;

final class BookingRescheduleRequest extends Model
{
    protected static string $table = 'booking_reschedule_requests';

    public static function pendingForBooking(int $bookingId): ?array
    {
        return self::whereFirst(['booking_id' => $bookingId, 'status' => 'pending']);
    }

    public static function pendingForInstructor(int $instructorId): array
    {
        $stmt = static::db()->prepare(
            'SELECT r.*, b.booking_ref, b.slot_date, b.start_time, b.client_id, u.name AS client_name, p.name AS package_name
             FROM booking_reschedule_requests r
             INNER JOIN bookings b ON b.id = r.booking_id
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.instructor_id = :instructor_id AND r.status = \'pending\'
             ORDER BY r.created_at ASC'
        );
        $stmt->execute(['instructor_id' => $instructorId]);

        return $stmt->fetchAll();
    }
}
