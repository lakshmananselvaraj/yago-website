<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\BookingRescheduleRequest;
use App\Models\BookingSlot;
use App\Models\Instructor;
use App\Models\Notification;
use App\Models\Package;
use App\Models\User;

final class BookingController extends Controller
{
    public function index(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $instructorId = (int) $instructor['id'];

        $bookings = Booking::where(['instructor_id' => $instructorId], 'slot_date DESC, start_time DESC');

        foreach ($bookings as &$booking) {
            $client = User::find((int) $booking['client_id']);
            $booking['client_name'] = $client['name'] ?? 'Client';
            $booking['package'] = Package::find($booking['package_id']);
        }
        unset($booking);

        $awaiting = array_values(array_filter($bookings, static fn (array $b): bool => $b['status'] === 'awaiting_trainer_approval'));
        $confirmed = array_values(array_filter($bookings, static fn (array $b): bool => in_array($b['status'], ['confirmed', 'completed'], true)));
        $other = array_values(array_filter($bookings, static fn (array $b): bool => in_array($b['status'], ['cancelled', 'rescheduled'], true)));

        $this->view('trainer/bookings', [
            'awaiting' => $awaiting,
            'confirmed' => $confirmed,
            'other' => $other,
            'rescheduleRequests' => BookingRescheduleRequest::pendingForInstructor($instructorId),
        ], 'dashboard');
    }

    public function accept(Request $request, string $ref): void
    {
        $booking = $this->ownedAwaitingBooking($ref);

        Booking::update($booking['id'], ['status' => 'confirmed']);
        ActivityLog::log(Auth::id(), 'booking_accepted', 'booking', (int) $booking['id'], ['booking_ref' => $booking['booking_ref']]);

        $this->sendConfirmedEmail($booking);

        $this->success(null, 'Booking confirmed.');
    }

    public function reject(Request $request, string $ref): void
    {
        $booking = $this->ownedAwaitingBooking($ref);

        Booking::update($booking['id'], ['status' => 'cancelled']);

        if ($booking['slot_id']) {
            BookingSlot::release((int) $booking['slot_id']);
        }

        ActivityLog::log(Auth::id(), 'booking_rejected', 'booking', (int) $booking['id'], ['booking_ref' => $booking['booking_ref']]);

        foreach (User::where(['role' => 'admin']) as $admin) {
            Notification::create(
                (int) $admin['id'],
                'booking_rejected',
                'Booking rejected — ' . $booking['booking_ref'],
                'A trainer rejected a paid booking. The client\'s payment needs manual review for a refund.'
            );
        }

        $this->sendRejectedEmail($booking);

        $this->success(null, 'Booking rejected. The client has been notified.');
    }

    public function approveReschedule(Request $request, int $id): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $reschedule = BookingRescheduleRequest::find($id);

        if (!$reschedule || $reschedule['status'] !== 'pending') {
            $this->fail('Reschedule request not found.', 404);
        }

        $booking = Booking::find((int) $reschedule['booking_id']);

        if (!$booking || (int) $booking['instructor_id'] !== (int) $instructor['id']) {
            $this->fail('Reschedule request not found.', 404);
        }

        $package = Package::find($booking['package_id']);
        BookingSlot::ensureGeneratedForDate((int) $booking['instructor_id'], $reschedule['requested_slot_date'], (int) $package['duration_minutes']);

        $newSlot = null;
        foreach (BookingSlot::findAvailable((int) $booking['instructor_id'], $reschedule['requested_slot_date']) as $candidate) {
            if (substr($candidate['start_time'], 0, 5) === substr($reschedule['requested_start_time'], 0, 5)) {
                $newSlot = $candidate;
                break;
            }
        }

        if (!$newSlot) {
            $this->fail('That time is no longer available. Ask the client to request a different time.', 409);
        }

        if (!BookingSlot::lockSlot((int) $newSlot['id'], (int) $booking['id'])) {
            $this->fail('That time slot was just booked by someone else.', 409);
        }

        $oldSlotId = $booking['slot_id'];

        Booking::update((int) $booking['id'], [
            'slot_id' => $newSlot['id'],
            'slot_date' => $newSlot['slot_date'],
            'start_time' => $newSlot['start_time'],
            'end_time' => $newSlot['end_time'],
        ]);

        if ($oldSlotId) {
            BookingSlot::release((int) $oldSlotId);
        }

        BookingRescheduleRequest::update($id, ['status' => 'approved']);
        ActivityLog::log(Auth::id(), 'reschedule_approved', 'booking', (int) $booking['id']);

        $this->sendRescheduleDecisionEmail($booking, true, $newSlot['slot_date'], $newSlot['start_time']);

        $this->success(null, 'Reschedule approved.');
    }

    public function declineReschedule(Request $request, int $id): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $reschedule = BookingRescheduleRequest::find($id);

        if (!$reschedule || $reschedule['status'] !== 'pending') {
            $this->fail('Reschedule request not found.', 404);
        }

        $booking = Booking::find((int) $reschedule['booking_id']);

        if (!$booking || (int) $booking['instructor_id'] !== (int) $instructor['id']) {
            $this->fail('Reschedule request not found.', 404);
        }

        BookingRescheduleRequest::update($id, ['status' => 'declined']);
        ActivityLog::log(Auth::id(), 'reschedule_declined', 'booking', (int) $booking['id']);

        $this->sendRescheduleDecisionEmail($booking, false, $reschedule['requested_slot_date'], $reschedule['requested_start_time']);

        $this->success(null, 'Reschedule request declined.');
    }

    private function sendRescheduleDecisionEmail(array $booking, bool $approved, string $date, string $time): void
    {
        $client = User::find((int) $booking['client_id']);

        if (!$client || !$client['email']) {
            return;
        }

        $ref = View::e($booking['booking_ref']);
        $dateLabel = View::e(date('l, d M Y', strtotime($date)));
        $timeLabel = View::e(date('g:i A', strtotime($time)));

        if ($approved) {
            Mailer::send($client['email'], 'Reschedule approved — ' . $booking['booking_ref'], <<<HTML
                <p>Your reschedule request for booking {$ref} was approved.</p>
                <p>Your session is now set for <strong>{$dateLabel} at {$timeLabel}</strong>.</p>
                HTML);
        } else {
            Mailer::send($client['email'], 'Reschedule request declined — ' . $booking['booking_ref'], <<<HTML
                <p>Your reschedule request for booking {$ref} could not be accommodated.</p>
                <p>Your original session time is unchanged. Please request a different time if needed.</p>
                HTML);
        }
    }

    private function ownedAwaitingBooking(string $ref): array
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['instructor_id'] !== (int) $instructor['id']) {
            $this->fail('Booking not found.', 404);
        }

        if ($booking['status'] !== 'awaiting_trainer_approval') {
            $this->fail('This booking is not awaiting your approval.', 422);
        }

        return $booking;
    }

    private function sendConfirmedEmail(array $booking): void
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
        $successUrl = $config['url'] . '/booking/' . $booking['booking_ref'] . '/success';

        Mailer::send($client['email'], 'Booking confirmed — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>Your booking is confirmed:</p>
            <p><strong>{$packageName}</strong> with {$instructorName}<br>{$dateLabel} at {$timeLabel}</p>
            <p>Reference: {$ref}</p>
            <p><a href="{$successUrl}">View booking</a></p>
            HTML);
    }

    private function sendRejectedEmail(array $booking): void
    {
        $client = User::find((int) $booking['client_id']);

        if (!$client || !$client['email']) {
            return;
        }

        $instructor = Instructor::findWithName((int) $booking['instructor_id']);
        $package = Package::find((int) $booking['package_id']);
        $clientName = View::e($client['name']);
        $ref = View::e($booking['booking_ref']);
        $dateLabel = View::e(date('l, d M Y', strtotime($booking['slot_date'])));
        $timeLabel = View::e(date('g:i A', strtotime($booking['start_time'])));
        $packageName = View::e($package['name'] ?? 'Session');
        $instructorName = View::e($instructor['name'] ?? 'your instructor');

        Mailer::send($client['email'], 'Booking could not be confirmed — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>Unfortunately {$instructorName} is unable to confirm your session for {$packageName} on {$dateLabel} at {$timeLabel} (Reference: {$ref}).</p>
            <p>Your payment will be refunded — our support team will be in touch shortly to process this.</p>
            HTML);
    }
}
