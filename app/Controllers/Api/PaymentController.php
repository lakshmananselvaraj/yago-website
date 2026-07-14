<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Payments\PaymentGatewayFactory;
use App\Core\Request;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Instructor;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;

final class PaymentController extends Controller
{
    public function createOrder(Request $request): void
    {
        $booking = $this->ownedPendingBooking($request);
        $gateway = PaymentGatewayFactory::make();

        if (!$gateway->isConfigured()) {
            $this->fail('Online payments are not configured yet. Please contact support to complete your booking.', 503);
        }

        $amountInSmallestUnit = (int) round(((float) $booking['total_amount']) * 100);
        $checkout = $gateway->createCheckout(
            $amountInSmallestUnit,
            $booking['currency'],
            $booking['booking_ref'],
            'Booking ' . $booking['booking_ref']
        );

        if (!$checkout) {
            $this->fail('Could not start the payment. Please try again.', 502);
        }

        Payment::createPending((int) $booking['id'], $gateway->name(), $checkout['order_id'], (float) $booking['total_amount'], $booking['currency']);

        $this->success($checkout);
    }

    public function verify(Request $request): void
    {
        $ref = (string) $request->input('booking_ref', '');
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->fail('Booking not found.', 404);
        }

        if (in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed'], true)) {
            $this->success(['redirect' => '/booking/' . $booking['booking_ref'] . '/success']);
        }

        $result = PaymentGatewayFactory::make()->confirmPayment($request);

        if (!$result['success']) {
            if ($result['order_id'] !== '') {
                Payment::markFailedByOrderId($result['order_id']);
            }
            ActivityLog::log(Auth::id(), 'payment_failed', 'booking', (int) $booking['id'], ['booking_ref' => $booking['booking_ref']]);
            $this->fail('Payment verification failed.', 422);
        }

        Payment::markSuccessByOrderId($result['order_id'], $result['transaction_id'] ?? $result['order_id']);
        Booking::update($booking['id'], ['status' => 'awaiting_trainer_approval']);
        ActivityLog::log(Auth::id(), 'payment_success', 'booking', (int) $booking['id'], [
            'booking_ref' => $booking['booking_ref'],
            'amount' => $booking['total_amount'],
        ]);

        $this->sendPaymentReceiptEmails($booking, $result['transaction_id'] ?? $result['order_id']);

        $this->success(['redirect' => '/booking/' . $booking['booking_ref'] . '/success']);
    }

    private function sendPaymentReceiptEmails(array $booking, ?string $transactionId): void
    {
        $client = User::find((int) $booking['client_id']);

        if (!$client || !$client['email']) {
            return;
        }

        $config = require dirname(__DIR__, 2) . '/Config/app.php';
        $instructor = Instructor::findWithName((int) $booking['instructor_id']);
        $package = Package::find((int) $booking['package_id']);
        $clientName = View::e($client['name']);
        $ref = View::e($booking['booking_ref']);
        $dateLabel = View::e(date('l, d M Y', strtotime($booking['slot_date'])));
        $timeLabel = View::e(date('g:i A', strtotime($booking['start_time'])));
        $packageName = View::e($package['name'] ?? 'Session');
        $instructorName = View::e($instructor['name'] ?? 'your instructor');
        $total = View::e($booking['currency']) . ' ' . number_format((float) $booking['total_amount'], 2);
        $successUrl = $config['url'] . '/booking/' . $booking['booking_ref'] . '/success';
        $invoiceUrl = $config['url'] . '/booking/' . $booking['booking_ref'] . '/invoice';
        $transactionIdSafe = View::e((string) $transactionId);

        Mailer::send($client['email'], 'Booking received — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>We've received your payment and your booking request has been sent to {$instructorName} for confirmation:</p>
            <p><strong>{$packageName}</strong> with {$instructorName}<br>{$dateLabel} at {$timeLabel}</p>
            <p>Reference: {$ref}</p>
            <p>You'll get an email as soon as it's confirmed.</p>
            <p><a href="{$successUrl}">View booking</a></p>
            HTML);

        Mailer::send($client['email'], 'Payment receipt — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>We've received your payment for booking {$ref}.</p>
            <p>Amount paid: <strong>{$total}</strong></p>
            <p>Transaction ID: {$transactionIdSafe}</p>
            <p><a href="{$invoiceUrl}">View invoice</a></p>
            HTML);

        Mailer::send($client['email'], 'Invoice — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>Your invoice for booking {$ref} is ready.</p>
            <p><strong>{$packageName}</strong> with {$instructorName} — Total {$total}</p>
            <p><a href="{$invoiceUrl}">View / print invoice</a></p>
            HTML);
    }

    private function ownedPendingBooking(Request $request): array
    {
        $ref = (string) $request->input('booking_ref', '');
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->fail('Booking not found.', 404);
        }

        if ($booking['status'] !== 'pending_payment') {
            $this->fail('This booking is not awaiting payment.', 422);
        }

        return $booking;
    }
}
