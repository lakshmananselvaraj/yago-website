<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

final class BookingSlot extends Model
{
    protected static string $table = 'booking_slots';

    public static function findAvailable(int $instructorId, string $date): array
    {
        return self::where(
            ['instructor_id' => $instructorId, 'slot_date' => $date, 'status' => 'available'],
            'start_time ASC'
        );
    }

    /**
     * Lazily materializes booking_slots rows for a given instructor+date from their
     * recurring/specific-date availability windows, split into $durationMinutes chunks.
     * Safe to call repeatedly — a unique (instructor_id, slot_date, start_time) key means
     * concurrent generation attempts just collide harmlessly and get ignored.
     */
    public static function ensureGeneratedForDate(int $instructorId, string $date, int $durationMinutes = 60): void
    {
        if (self::count(['instructor_id' => $instructorId, 'slot_date' => $date]) > 0) {
            return;
        }

        foreach (InstructorBlockedDate::forInstructor($instructorId) as $blocked) {
            if ($blocked['blocked_date'] === $date) {
                return;
            }
        }

        $dayOfWeek = (int) date('w', strtotime($date));
        $now = time();

        foreach (InstructorAvailability::forInstructor($instructorId) as $window) {
            $matches = (int) $window['is_recurring'] === 1
                ? (int) $window['day_of_week'] === $dayOfWeek
                : $window['specific_date'] === $date;

            if (!$matches) {
                continue;
            }

            $stepSeconds = max($durationMinutes, 1) * 60;
            $start = strtotime($date . ' ' . $window['start_time']);
            $end = strtotime($date . ' ' . $window['end_time']);

            for ($slotStart = $start; $slotStart + $stepSeconds <= $end; $slotStart += $stepSeconds) {
                if ($slotStart <= $now) {
                    continue;
                }

                try {
                    self::insert([
                        'instructor_id' => $instructorId,
                        'slot_date' => $date,
                        'start_time' => date('H:i:s', $slotStart),
                        'end_time' => date('H:i:s', $slotStart + $stepSeconds),
                        'status' => 'available',
                    ]);
                } catch (PDOException) {
                    // another request generated this exact slot first — fine, it exists now.
                }
            }
        }
    }

    public static function lockSlot(int $slotId, int $bookingId): bool
    {
        // Conditional UPDATE guarantees only one concurrent request can claim the slot;
        // rowCount() of 0 means someone else already booked it, so the caller must reject/rollback.
        $stmt = static::db()->prepare(
            "UPDATE booking_slots SET status = 'booked', booking_id = :booking_id WHERE id = :id AND status = 'available'"
        );
        $stmt->execute(['booking_id' => $bookingId, 'id' => $slotId]);

        return $stmt->rowCount() > 0;
    }

    public static function release(int $slotId): void
    {
        self::update($slotId, ['status' => 'available', 'booking_id' => null]);
    }
}
