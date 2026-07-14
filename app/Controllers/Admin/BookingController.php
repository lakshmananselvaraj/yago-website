<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Instructor;
use App\Models\MeetingLink;
use App\Models\Package;
use App\Models\User;

final class BookingController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): void
    {
        $status = $request->query('status') ?: null;
        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $db = Database::connection();

        $countStmt = $db->prepare('SELECT COUNT(*) c FROM bookings b' . ($status ? ' WHERE b.status = :status' : ''));
        $countStmt->execute($status ? ['status' => $status] : []);
        $total = (int) $countStmt->fetch()['c'];

        $sql = 'SELECT b.*, u.name AS client_name, u.email AS client_email,
                       iu.name AS instructor_name, p.name AS package_name
                FROM bookings b
                INNER JOIN users u ON u.id = b.client_id
                INNER JOIN instructors i ON i.id = b.instructor_id
                INNER JOIN users iu ON iu.id = i.user_id
                INNER JOIN packages p ON p.id = b.package_id';

        $params = [];
        if ($status) {
            $sql .= ' WHERE b.status = :status';
            $params['status'] = $status;
        }
        $sql .= sprintf(' ORDER BY b.created_at DESC LIMIT %d OFFSET %d', self::PER_PAGE, $offset);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();

        foreach ($bookings as &$booking) {
            $booking['meeting_link'] = MeetingLink::forBooking((int) $booking['id']);
        }
        unset($booking);

        $this->view('admin/bookings', [
            'bookings' => $bookings,
            'selectedStatus' => $status,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / self::PER_PAGE)),
        ], 'dashboard');
    }

    public function saveMeetingLink(Request $request, string $ref): void
    {
        $booking = Booking::findByRef($ref);

        if (!$booking) {
            $this->fail('Booking not found.', 404);
        }

        $provider = (string) $request->input('provider', '');
        $url = (string) $request->input('url', '');

        if (!in_array($provider, ['google_meet', 'zoom'], true) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->fail('Please provide a valid provider and a valid URL.', 422);
        }

        MeetingLink::upsertForBooking((int) $booking['id'], $provider, $url, Auth::id());
        ActivityLog::log(Auth::id(), 'meeting_link_attached', 'booking', (int) $booking['id'], ['provider' => $provider]);

        $this->success(null, 'Meeting link saved.');
    }

    public function reschedule(Request $request, string $ref): void
    {
        $booking = Booking::findByRef($ref);

        if (!$booking) {
            $this->fail('Booking not found.', 404);
        }

        if (!in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed'], true)) {
            $this->fail('Only awaiting-approval or confirmed bookings can be rescheduled.', 422);
        }

        $slotDate = (string) $request->input('slot_date', '');
        $startTime = (string) $request->input('start_time', '');

        if ($slotDate === '' || $startTime === '') {
            $this->fail('Please choose a new date and time.', 422);
        }

        $package = Package::find($booking['package_id']);
        BookingSlot::ensureGeneratedForDate((int) $booking['instructor_id'], $slotDate, (int) $package['duration_minutes']);

        $newSlot = null;
        foreach (BookingSlot::findAvailable((int) $booking['instructor_id'], $slotDate) as $candidate) {
            if (substr($candidate['start_time'], 0, 5) === substr($startTime, 0, 5)) {
                $newSlot = $candidate;
                break;
            }
        }

        if (!$newSlot) {
            $this->fail('That time slot is not available for this instructor.', 409);
        }

        if (!BookingSlot::lockSlot((int) $newSlot['id'], (int) $booking['id'])) {
            $this->fail('That time slot was just booked by someone else. Please choose another.', 409);
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

        ActivityLog::log(Auth::id(), 'booking_rescheduled', 'booking', (int) $booking['id'], [
            'booking_ref' => $booking['booking_ref'],
            'new_date' => $newSlot['slot_date'],
            'new_time' => $newSlot['start_time'],
        ]);

        $this->sendRescheduleEmail($booking, $newSlot);

        $this->success(null, 'Booking rescheduled.');
    }

    private function sendRescheduleEmail(array $booking, array $newSlot): void
    {
        $client = User::find((int) $booking['client_id']);

        if (!$client || !$client['email']) {
            return;
        }

        $instructor = Instructor::findWithName((int) $booking['instructor_id']);
        $clientName = htmlspecialchars($client['name'], ENT_QUOTES);
        $ref = htmlspecialchars($booking['booking_ref'], ENT_QUOTES);
        $instructorName = htmlspecialchars($instructor['name'] ?? 'your instructor', ENT_QUOTES);
        $dateLabel = htmlspecialchars(date('l, d M Y', strtotime($newSlot['slot_date'])), ENT_QUOTES);
        $timeLabel = htmlspecialchars(date('g:i A', strtotime($newSlot['start_time'])), ENT_QUOTES);

        Mailer::send($client['email'], 'Booking rescheduled — ' . $booking['booking_ref'], <<<HTML
            <p>Hi {$clientName},</p>
            <p>Your session with {$instructorName} (Reference: {$ref}) has been rescheduled to:</p>
            <p><strong>{$dateLabel} at {$timeLabel}</strong></p>
            HTML);
    }
}
