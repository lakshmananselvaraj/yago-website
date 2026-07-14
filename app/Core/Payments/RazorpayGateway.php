<?php

namespace App\Core\Payments;

use App\Core\Request;

final class RazorpayGateway implements PaymentGateway
{
    private const API_BASE = 'https://api.razorpay.com/v1';

    public function name(): string
    {
        return 'razorpay';
    }

    public function isConfigured(): bool
    {
        $config = self::config();

        return $config['key_id'] !== '' && $config['key_secret'] !== '';
    }

    public function createCheckout(int $amountInSmallestUnit, string $currency, string $receipt, string $description): ?array
    {
        $config = self::config();

        $ch = curl_init(self::API_BASE . '/orders');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERPWD => $config['key_id'] . ':' . $config['key_secret'],
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amountInSmallestUnit,
                'currency' => $currency,
                'receipt' => $receipt,
                'payment_capture' => 1,
            ]),
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ok = curl_errno($ch) === 0;
        curl_close($ch);

        if (!$ok || $body === false || $status >= 300) {
            return null;
        }

        $order = json_decode($body, true);

        if (!is_array($order) || !isset($order['id'])) {
            return null;
        }

        return [
            'provider' => 'razorpay',
            'order_id' => $order['id'],
            'amount' => $amountInSmallestUnit,
            'currency' => $currency,
            'key_id' => $config['key_id'],
            'name' => 'Vipasa Yoga',
            'description' => $description,
        ];
    }

    public function confirmPayment(Request $request): array
    {
        $orderId = (string) $request->input('razorpay_order_id', '');
        $paymentId = (string) $request->input('razorpay_payment_id', '');
        $signature = (string) $request->input('razorpay_signature', '');

        if ($orderId === '' || $paymentId === '' || $signature === '') {
            return ['success' => false, 'order_id' => $orderId, 'transaction_id' => null];
        }

        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, self::config()['key_secret']);

        if (!hash_equals($expected, $signature)) {
            return ['success' => false, 'order_id' => $orderId, 'transaction_id' => null];
        }

        return ['success' => true, 'order_id' => $orderId, 'transaction_id' => $paymentId];
    }

    private static function config(): array
    {
        return (require dirname(__DIR__, 2) . '/Config/app.php')['payments']['razorpay'];
    }
}
