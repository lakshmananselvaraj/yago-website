<?php

namespace App\Core\Payments;

use App\Core\Request;

interface PaymentGateway
{
    public function name(): string;

    public function isConfigured(): bool;

    /**
     * Starts a checkout for $amountInSmallestUnit (paise/cents) and returns a
     * gateway-neutral payload the frontend uses to launch the right checkout
     * SDK. Always includes a 'provider' key and an 'order_id' key (the id the
     * `payments` row is tracked against). Returns null on any failure.
     */
    public function createCheckout(int $amountInSmallestUnit, string $currency, string $receipt, string $description): ?array;

    /**
     * Verifies a client-reported payment result against the gateway itself
     * (never trusts the client's claim alone). Always returns an array —
     * ['success' => false, 'order_id' => '', 'transaction_id' => null] shape
     * on any failure/missing input, never null, so the caller can uniformly
     * mark the matching `payments` row failed by order_id when available.
     *
     * @return array{success: bool, order_id: string, transaction_id: ?string}
     */
    public function confirmPayment(Request $request): array;
}
