<?php

namespace App\Core\Payments;

use App\Core\Request;

/**
 * Uses Stripe Checkout Sessions (a Stripe-hosted payment page, redirect-based)
 * rather than embedded Elements — this app has no SPA/build step to host a
 * mounted card field, and a redirect mirrors the "leave the page, come back
 * confirmed" shape the Razorpay modal flow already has.
 */
final class StripeGateway implements PaymentGateway
{
    private const API_BASE = 'https://api.stripe.com/v1';

    public function name(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        $config = self::config();

        return $config['public_key'] !== '' && $config['secret_key'] !== '';
    }

    public function createCheckout(int $amountInSmallestUnit, string $currency, string $receipt, string $description): ?array
    {
        $appUrl = (require dirname(__DIR__, 2) . '/Config/app.php')['url'];

        $session = $this->request('POST', '/checkout/sessions', [
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($currency),
                    'unit_amount' => $amountInSmallestUnit,
                    'product_data' => ['name' => $description],
                ],
            ]],
            'success_url' => $appUrl . '/booking/confirm/' . $receipt . '?stripe_session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $appUrl . '/booking/confirm/' . $receipt,
        ]);

        if (!$session || !isset($session['id'], $session['url'])) {
            return null;
        }

        return [
            'provider' => 'stripe',
            'order_id' => $session['id'],
            'redirect_url' => $session['url'],
            'amount' => $amountInSmallestUnit,
            'currency' => $currency,
        ];
    }

    public function confirmPayment(Request $request): array
    {
        $sessionId = (string) $request->input('stripe_session_id', '');

        if ($sessionId === '') {
            return ['success' => false, 'order_id' => '', 'transaction_id' => null];
        }

        // Never trust the client's claim of success — re-fetch the Checkout
        // Session from Stripe's API server-side and check its status directly.
        $session = $this->request('GET', '/checkout/sessions/' . urlencode($sessionId));

        if (!$session || ($session['payment_status'] ?? '') !== 'paid') {
            return ['success' => false, 'order_id' => $sessionId, 'transaction_id' => null];
        }

        $paymentIntentId = is_array($session['payment_intent'] ?? null)
            ? ($session['payment_intent']['id'] ?? $sessionId)
            : ($session['payment_intent'] ?? $sessionId);

        return ['success' => true, 'order_id' => $sessionId, 'transaction_id' => (string) $paymentIntentId];
    }

    private function request(string $method, string $path, array $params = []): ?array
    {
        $config = self::config();

        $ch = curl_init(self::API_BASE . $path);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERPWD => $config['secret_key'] . ':',
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }

        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ok = curl_errno($ch) === 0;
        curl_close($ch);

        if (!$ok || $body === false || $status >= 300) {
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }

    private static function config(): array
    {
        return (require dirname(__DIR__, 2) . '/Config/app.php')['payments']['stripe'];
    }
}
