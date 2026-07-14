<?php

namespace App\Core\Payments;

use App\Core\Request;

/**
 * Local stand-in used only when payments.mode is 'test' and no real gateway
 * keys are configured — lets the full booking flow (including the payment
 * step) be exercised end-to-end without a Razorpay/Stripe account. Never
 * used when real keys are present, regardless of mode.
 */
final class MockGateway implements PaymentGateway
{
    public function name(): string
    {
        // The `payments.gateway` column is an enum without a 'mock' value;
        // 'other' is the existing catch-all bucket for non-standard gateways,
        // so this is stored there rather than requiring a schema migration.
        // The frontend still sees 'mock' via createCheckout()'s 'provider' key.
        return 'other';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createCheckout(int $amountInSmallestUnit, string $currency, string $receipt, string $description): ?array
    {
        return [
            'provider' => 'mock',
            'order_id' => 'mock_' . bin2hex(random_bytes(8)),
            'amount' => $amountInSmallestUnit,
            'currency' => $currency,
            'key_id' => '',
            'name' => 'Vipasa Yoga',
            'description' => $description,
        ];
    }

    public function confirmPayment(Request $request): array
    {
        $orderId = (string) $request->input('mock_order_id', '');

        if ($orderId === '') {
            return ['success' => false, 'order_id' => '', 'transaction_id' => null];
        }

        return ['success' => true, 'order_id' => $orderId, 'transaction_id' => 'mock_txn_' . substr($orderId, 5)];
    }
}
