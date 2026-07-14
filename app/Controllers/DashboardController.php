<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Booking;
use App\Models\BookingRescheduleRequest;
use App\Models\Favorite;
use App\Models\Instructor;
use App\Models\InstructorReview;
use App\Models\MeetingLink;
use App\Models\Notification;
use App\Models\Package;
use App\Models\Payment;

final class DashboardController extends Controller
{
    private const WELLNESS_QUOTES = [
        'Flow like a river — steady, unhurried, and always moving forward.',
        'The body benefits from movement, and the mind benefits from stillness.',
        'Progress is not linear. Show up for yourself today, exactly as you are.',
        'Breath is the bridge between the body and the mind.',
        'Small, consistent practice beats occasional intensity.',
        'You are not behind. You are exactly where your practice needs you to be.',
        'Every session is a return to yourself.',
    ];

    public function index(Request $request): void
    {
        $bookings = Booking::forClient(Auth::id());

        $nextSession = null;
        $completedCount = 0;

        foreach ($bookings as $booking) {
            if ($booking['status'] === 'completed') {
                $completedCount++;
            }

            $isFuture = strtotime($booking['slot_date'] . ' ' . $booking['start_time']) > time();
            $isBookable = $isFuture && in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed'], true);

            if ($isBookable && ($nextSession === null || $booking['slot_date'] . $booking['start_time'] < $nextSession['slot_date'] . $nextSession['start_time'])) {
                $nextSession = $booking;
            }
        }

        if ($nextSession !== null) {
            $nextSession['instructor'] = Instructor::findWithName($nextSession['instructor_id']);
            $nextSession['package'] = Package::find($nextSession['package_id']);
        }

        $quote = self::WELLNESS_QUOTES[(int) date('z') % count(self::WELLNESS_QUOTES)];

        $this->view('dashboard/index', [
            'nextSession' => $nextSession,
            'completedCount' => $completedCount,
            'upcomingCount' => count(array_filter($bookings, static fn (array $b): bool => in_array($b['status'], ['awaiting_trainer_approval', 'confirmed'], true) && strtotime($b['slot_date'] . ' ' . $b['start_time']) > time())),
            'favoriteCount' => count(Favorite::forClient(Auth::id())),
            'weekStreak' => $this->computeWeekStreak($bookings),
            'wellnessQuote' => $quote,
        ], 'dashboard');
    }

    /**
     * Consecutive weeks (Mon-Sun) with at least one completed session, walking
     * backward from today. The current week doesn't have to be done yet — an
     * in-progress week just isn't counted, so it can't falsely break a streak.
     */
    private function computeWeekStreak(array $bookings): int
    {
        $weeksWithSession = [];

        foreach ($bookings as $booking) {
            if ($booking['status'] === 'completed') {
                $weeksWithSession[date('o-W', strtotime($booking['slot_date']))] = true;
            }
        }

        if (empty($weeksWithSession)) {
            return 0;
        }

        $cursor = time();
        if (!isset($weeksWithSession[date('o-W', $cursor)])) {
            $cursor = strtotime('-7 days', $cursor);
        }

        $streak = 0;
        while (isset($weeksWithSession[date('o-W', $cursor)])) {
            $streak++;
            $cursor = strtotime('-7 days', $cursor);
        }

        return $streak;
    }

    public function notifications(Request $request): void
    {
        $notifications = Notification::forUser(Auth::id(), 100);

        foreach ($notifications as $notification) {
            if (!$notification['is_read']) {
                Notification::markRead((int) $notification['id']);
            }
        }

        $this->view('dashboard/notifications', ['notifications' => $notifications], 'dashboard');
    }

    public function bookings(Request $request): void
    {
        $bookings = Booking::forClient(Auth::id());

        foreach ($bookings as &$booking) {
            $booking['instructor'] = Instructor::findWithName($booking['instructor_id']);
            $booking['package'] = Package::find($booking['package_id']);
            $booking['meeting_link'] = MeetingLink::forBooking($booking['id']);
            $booking['can_rate'] = $booking['status'] === 'completed' && !InstructorReview::existsForBooking($booking['id']);
            $booking['has_pending_reschedule'] = $booking['status'] === 'confirmed' && BookingRescheduleRequest::pendingForBooking($booking['id']) !== null;
        }
        unset($booking);

        $upcoming = [];
        $history = [];

        foreach ($bookings as $booking) {
            $isFuture = strtotime($booking['slot_date'] . ' ' . $booking['start_time']) > time();
            $isUpcoming = $isFuture && in_array($booking['status'], ['pending_payment', 'awaiting_trainer_approval', 'confirmed'], true);

            if ($isUpcoming) {
                $upcoming[] = $booking;
            } else {
                $history[] = $booking;
            }
        }

        $this->view('dashboard/bookings', ['upcoming' => $upcoming, 'history' => $history], 'dashboard');
    }

    public function payments(Request $request): void
    {
        $payments = Payment::forClient(Auth::id());

        $this->view('dashboard/payments', ['payments' => $payments], 'dashboard');
    }

    public function invoices(Request $request): void
    {
        $bookings = array_values(array_filter(
            Booking::forClient(Auth::id()),
            static fn (array $b): bool => in_array($b['status'], ['awaiting_trainer_approval', 'confirmed', 'completed'], true)
        ));

        foreach ($bookings as &$booking) {
            $booking['instructor'] = Instructor::findWithName($booking['instructor_id']);
            $booking['package'] = Package::find($booking['package_id']);
        }
        unset($booking);

        $this->view('dashboard/invoices', ['bookings' => $bookings], 'dashboard');
    }

    public function favorites(Request $request): void
    {
        $this->view('dashboard/favorites', ['favorites' => Favorite::forClient(Auth::id())], 'dashboard');
    }
}
