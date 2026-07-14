<?php

namespace App\Core\Payments;

final class PaymentGatewayFactory
{
    public static function make(): PaymentGateway
    {
        $payments = (require dirname(__DIR__, 2) . '/Config/app.php')['payments'];

        $gateway = match ($payments['gateway']) {
            'stripe' => new StripeGateway(),
            default => new RazorpayGateway(),
        };

        // No real keys set for the active gateway — fall back to the mock
        // gateway in test mode so bookings can still be completed end-to-end
        // during local dev/demo. Never falls back in production mode: a
        // misconfigured live deployment should surface the "not configured"
        // notice instead of silently faking payments.
        if ($payments['mode'] === 'test' && !$gateway->isConfigured()) {
            return new MockGateway();
        }

        return $gateway;
    }
}
