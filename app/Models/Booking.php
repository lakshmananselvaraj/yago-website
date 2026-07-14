<?php

namespace App\Models;

use App\Core\Model;

final class Booking extends Model
{
    protected static string $table = 'bookings';

    public static function findByRef(string $ref): ?array
    {
        return self::findBy('booking_ref', $ref);
    }

    public static function generateUniqueRef(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        do {
            $suffix = '';
            for ($i = 0; $i < 8; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $ref = 'VY' . $suffix;
        } while (self::findByRef($ref) !== null);

        return $ref;
    }

    public static function forClient(int $clientId): array
    {
        return self::where(['client_id' => $clientId], 'created_at DESC');
    }
}
