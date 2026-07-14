<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\View;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\BookingRescheduleRequest;
use App\Models\BookingSlot;
use App\Models\Coupon;
use App\Models\Instructor;
use App\Models\InstructorReview;
use App\Models\InstructorService;
use App\Models\Notification;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;

final class BookingController extends Controller
{
    public function store(Request $request): void
    {
        $instructorId = (int) $request->input('instructor_id');
        $packageId = (int) $request->input('package_id');
        $slotDate = (string) $request->input('slot_date', '');
        $startTime = (string) $request->input('start_time', '');
        $couponCode = trim((string) $request->input('coupon_code', ''));
        $notes = trim((string) $request->input('notes', ''));

        if (!$instructorId || !$packageId || $slotDate === '' || $startTime === '') {
            $this->fail('Instructor, package, date, and time are required.', 422);
        }

        $instructor = Instructor::find($instructorId);
        $package = Package::find($packageId);

        if (!$instructor || !$package) {
            $this->fail('The selected instructor or package could not be found.', 404);
        }

        $price = InstructorService::effectivePrice($instructorId, $packageId);
        if ($price === null) {
            $this->fail('This instructor does not currently offer that package.', 422);
        }

        BookingSlot::ensureGeneratedForDate($instructorId, $slotDate, (int) $package['duration_minutes']);
        $slot = null;
        foreach (BookingSlot::findAvailable($instructorId, $slotDate) as $candidate) {
            if (substr($candidate['start_time'], 0, 5) === substr($startTime, 0, 5)) {
                $slot = $candidate;
                break;
            }
        }

        if (!$slot) {
            $this->fail('That time slot is no longer available. Please choose another.', 409);
        }

        $taxPercent = (float) Setting::get('tax_percent', 5);
        $taxAmount = round($price * $taxPercent / 100, 2);

        $discountAmount = 0.0;
        $couponId = null;

        if ($couponCode !== '') {
            $coupon = Coupon::findValidByCode(strtoupper($couponCode));
            if (!$coupon) {
                $this->fail('This coupon code is invalid or has expired.', 422, ['coupon_code' => ['Invalid coupon.']]);
            }
            if ($price < (float) $coupon['min_order_amount']) {
                $this->fail(sprintf('This coupon requires a minimum order of %.2f.', $coupon['min_order_amount']), 422);
            }
            $discountAmount = $coupon['discount_type'] === 'percent'
                ? round($price * ((float) $coupon['discount_value'] / 100), 2)
                : (float) $coupon['discount_value'];
            $discountAmount = min($discountAmount, $price);
            $couponId = $coupon['id'];
        }

        $total = max(0, round($price + $taxAmount - $discountAmount, 2));

        $bookingId = Booking::insert([
            'booking_ref' => Booking::generateUniqueRef(),
            'client_id' => Auth::id(),
            'instructor_id' => $instructorId,
            'package_id' => $packageId,
            'slot_id' => $slot['id'],
            'slot_date' => $slotDate,
            'start_time' => $slot['start_time'],
            'end_time' => $slot['end_time'],
            'client_timezone' => (string) $request->input('timezone', 'UTC'),
            'status' => 'pending_payment',
            'price' => $price,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'coupon_id' => $couponId,
            'total_amount' => $total,
            'currency' => $package['currency'],
            'notes' => $notes !== '' ? $notes : null,
        ]);

        if (!BookingSlot::lockSlot((int) $slot['id'], $bookingId)) {
            Booking::delete($bookingId);
            $this->fail('That time slot was just booked by someone else. Please choose another.', 409);
        }

        if ($couponId) {
            Coupon::incrementUsage($couponId);
        }

        $booking = Booking::find($bookingId);
        ActivityLog::log(Auth::id(), 'booking_created', 'booking', $bookingId, ['booking_ref' => $booking['booking_ref']]);

        $this->success([
            'booking' => $booking,
            'redirect' => '/booking/confirm/' . $booking['booking_ref'],
        ], 'Booking created — proceed to payment.', 201);
    }

    public function show(Request $request, string $ref): void
    {
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->fail('Booking not found.', 404);
        }

        $this->success([
            'booking' => $booking,
            'instructor' => Instructor::findWithName($booking['instructor_id']),
            'package' => Package::find($booking['package_id']),
        ]);
    }

    public function cancel(Request $request, string $ref): void
    {
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->fail('Booking not found.', 404);
        }

        if (!in_array($booking['status'], ['pending_payment', 'awaiting_trainer_approval', 'confirmed'], true)) {
            $this->fail('This booking can no longer be cancelled.', 422);
        }

        if (strtotime($booking['slot_date'] . ' ' . $booking['start_time']) <= time()) {
            $this->fail('This session has already started or passed and can no longer be cancelled.', 422);
        }

        Booking::update((int) $booking['id'], ['status' => 'cancelled']);

        if ($booking['slot_id']) {
            BookingSlot::release((int) $booking['slot_id']);
        }

        ActivityLog::log(Auth::id(), 'booking_cancelled', 'booking', (int) $booking['id'], ['booking_ref' => $booking['booking_ref']]);
        $this->sendCancellationEmail($booking);

        $this->success(null, 'Booking cancelled.');
    }

    public function review(Request $request, string $ref): void
    {
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->fail('Booking not found.', 404);
        }

        if ($booking['status'] !== 'completed') {
            $this->fail('You can only rate a trainer after the session is completed.', 422);
        }

        if (InstructorReview::existsForBooking((int) $booking['id'])) {
            $this->fail('You have already rated this session.', 422);
        }

        $rating = (int) $request->input('rating', 0);

        if ($rating < 1 || $rating > 5) {
            $this->fail('Please choose a rating from 1 to 5.', 422);
        }

        $reviewText = trim((string) $request->input('review_text', '')) ?: null;

        InstructorReview::create([
            'instructor_id' => (int) $booking['instructor_id'],
            'client_id' => Auth::id(),
            'booking_id' => (int) $booking['id'],
            'rating' => $rating,
            'review_text' => $reviewText,
        ]);

        ActivityLog::log(Auth::id(), 'instructor_rated', 'booking', (int) $booking['id'], ['rating' => $rating]);

        $this->success(null, 'Thanks for your feedback!');
    }

    public function requestReschedule(Request $request, string $ref): void
    {
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['client_id'] !== Auth::id()) {
            $this->fail('Booking not found.', 404);
        }

        if ($booking['status'] !== 'confirmed') {
            $this->fail('Only confirmed bookings can be rescheduled.', 422);
        }

        if (strtotime($booking['slot_date'] . ' ' . $booking['start_time']) <= time()) {
            $this->fail('This session has already started or passed.', 422);
        }

        if (BookingRescheduleRequest::pendingForBooking((int) $booking['id'])) {
            $this->fail('You already have a pending reschedule request for this booking.', 422);
        }

        $slotDate = (string) $request->input('slot_date', '');
        $startTime = (string) $request->input('start_time', '');

        if ($slotDate === '' || $startTime === '') {
            $this->fail('Please choose a new date and time.', 422);
        }

        $id = BookingRescheduleRequest::insert([
            'booking_id' => (int) $booking['id'],
            'requested_slot_date' => $slotDate,
            'requested_start_time' => $startTime,
            'status' => 'pending',
        ]);

        ActivityLog::log(Auth::id(), 'reschedule_requested', 'booking', (int) $booking['id'], [
            'booking_ref' => $booking['booking_ref'],
            'requested_date' => $slotDate,
            'requested_time' => $startTime,
        ]);

        $instructor = Instructor::findWithName((int) $booking['instructor_id']);
        if ($instructor) {
            Notification::create(
                (int) $instructor['user_id'],
                'reschedule_requested',
                'Reschedule request — ' . $booking['booking_ref'],
                sprintf('A client has requested to move their session to %s at %s.', $slotDate, date('g:i A', strtotime($startTime)))
            );
        }

        $this->success(['id' => $id], 'Reschedule request sent to your instructor.');
    }

    private function sendCancellationEmail(array $booking): void
    {
        $client = User::find((int) $booking['client_id']);

        if (!$client || !$client['email']) {
            return;
        }

        $instructor = Instructor::findWithName((int) $booking['instructor_id']);
        $package = Package::find((int) $booking['package_id']);
        $clientName = View::e($client['name']);
        $ref = View::e($booking['booking_ref']);
        $packageName = View::e($package['name'] ?? 'Session');
        $instructorName = View::e($instructor['name'] ?? 'your instructor');
        $dateLabel = View::e(date('l, d M Y', strtotime($booking['slot_date'])));
        $timeLabel = View::e(date('g:i A', strtotime($booking['start_time'])));

        Mailer::send($client['email'], 'Booking cancelled — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>Your booking has been cancelled as requested:</p>
            <p><strong>{$packageName}</strong> with {$instructorName}<br>{$dateLabel} at {$timeLabel}</p>
            <p>Reference: {$ref}</p>
            HTML);
    }
}
