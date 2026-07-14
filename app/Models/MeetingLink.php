<?php

namespace App\Models;

use App\Core\Model;

final class MeetingLink extends Model
{
    protected static string $table = 'meeting_links';

    public static function forBooking(int $bookingId): ?array
    {
        return self::whereFirst(['booking_id' => $bookingId]);
    }

    public static function upsertForBooking(int $bookingId, string $provider, string $url, int $createdBy): void
    {
        $existing = self::forBooking($bookingId);

        if ($existing) {
            self::update($existing['id'], ['provider' => $provider, 'url' => $url, 'created_by' => $createdBy]);
            return;
        }

        self::insert([
            'booking_id' => $bookingId,
            'provider' => $provider,
            'url' => $url,
            'created_by' => $createdBy,
        ]);
    }
}
