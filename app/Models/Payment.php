<?php

namespace App\Models;

use App\Core\Model;

final class Payment extends Model
{
    protected static string $table = 'payments';

    public static function createPending(int $bookingId, string $gateway, string $orderId, float $amount, string $currency): int
    {
        return self::insert([
            'booking_id' => $bookingId,
            'gateway' => $gateway,
            'gateway_order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
        ]);
    }

    public static function findByOrderId(string $orderId): ?array
    {
        return self::whereFirst(['gateway_order_id' => $orderId]);
    }

    public static function markSuccessByOrderId(string $orderId, string $paymentId): void
    {
        $payment = self::findByOrderId($orderId);

        if ($payment) {
            self::update($payment['id'], ['status' => 'success', 'gateway_txn_id' => $paymentId]);
        }
    }

    public static function markFailedByOrderId(string $orderId): void
    {
        $payment = self::findByOrderId($orderId);

        if ($payment) {
            self::update($payment['id'], ['status' => 'failed']);
        }
    }

    public static function forBooking(int $bookingId): ?array
    {
        $rows = self::where(['booking_id' => $bookingId], 'id DESC', 1);

        return $rows[0] ?? null;
    }

    public static function forClient(int $clientId): array
    {
        $stmt = static::db()->prepare(
            'SELECT pay.*, b.booking_ref
             FROM payments pay
             INNER JOIN bookings b ON b.id = pay.booking_id
             WHERE b.client_id = :client_id
             ORDER BY pay.created_at DESC'
        );
        $stmt->execute(['client_id' => $clientId]);

        return $stmt->fetchAll();
    }
}
