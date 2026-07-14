<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Instructor;
use App\Models\InstructorAvailability;
use App\Models\InstructorBlockedDate;

final class CalendarController extends Controller
{
    public function index(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $instructorId = (int) $instructor['id'];

        $view = (string) $request->query('view', 'month');
        if (!in_array($view, ['month', 'week', 'day'], true)) {
            $view = 'month';
        }

        $month = (int) $request->query('month', date('n'));
        $year = (int) $request->query('year', date('Y'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 1970 || $year > 2100) {
            $year = (int) date('Y');
        }

        $db = Database::connection();
        $firstDay = sprintf('%04d-%02d-01', $year, $month);
        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $lastDay = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $stmt = $db->prepare(
            'SELECT b.*, u.name AS client_name, p.name AS package_name
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.instructor_id = :instructor_id AND b.slot_date BETWEEN :first_day AND :last_day
               AND b.status IN (\'awaiting_trainer_approval\',\'confirmed\',\'completed\')
             ORDER BY b.slot_date ASC, b.start_time ASC'
        );
        $stmt->execute(['instructor_id' => $instructorId, 'first_day' => $firstDay, 'last_day' => $lastDay]);

        $byDate = [];
        foreach ($stmt->fetchAll() as $booking) {
            $byDate[$booking['slot_date']][] = $booking;
        }

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $rawDate = (string) $request->query('date', date('Y-m-d'));
        $anchorTimestamp = strtotime($rawDate);
        $anchor = $anchorTimestamp !== false ? date('Y-m-d', $anchorTimestamp) : date('Y-m-d');

        $weekData = null;
        $dayData = null;

        if ($view === 'week') {
            $weekData = $this->weekData($db, $instructorId, $anchor);
        } elseif ($view === 'day') {
            $dayData = $this->dayData($db, $instructorId, $anchor);
        }

        $this->view('trainer/calendar', [
            'view' => $view,
            'anchor' => $anchor,
            'byDate' => $byDate,
            'month' => $month,
            'year' => $year,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'weekData' => $weekData,
            'dayData' => $dayData,
            'windows' => InstructorAvailability::forInstructor($instructorId),
            'blockedDates' => InstructorBlockedDate::forInstructor($instructorId),
            'instructorId' => $instructorId,
        ], 'dashboard');
    }

    private function weekData(\PDO $db, int $instructorId, string $anchor): array
    {
        $dayOfWeek = (int) date('N', strtotime($anchor)); // 1=Mon..7=Sun
        $monday = date('Y-m-d', strtotime($anchor . " -" . ($dayOfWeek - 1) . ' days'));
        $sunday = date('Y-m-d', strtotime($monday . ' +6 days'));

        $stmt = $db->prepare(
            'SELECT b.*, u.name AS client_name, p.name AS package_name
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.instructor_id = :instructor_id AND b.slot_date BETWEEN :start AND :end
               AND b.status IN (\'awaiting_trainer_approval\',\'confirmed\',\'completed\')
             ORDER BY b.slot_date ASC, b.start_time ASC'
        );
        $stmt->execute(['instructor_id' => $instructorId, 'start' => $monday, 'end' => $sunday]);

        $byDate = [];
        foreach ($stmt->fetchAll() as $booking) {
            $byDate[$booking['slot_date']][] = $booking;
        }

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($monday . " +{$i} days"));
            $days[] = ['date' => $date, 'bookings' => $byDate[$date] ?? []];
        }

        return [
            'start' => $monday,
            'end' => $sunday,
            'prevAnchor' => date('Y-m-d', strtotime($monday . ' -7 days')),
            'nextAnchor' => date('Y-m-d', strtotime($monday . ' +7 days')),
            'days' => $days,
        ];
    }

    private function dayData(\PDO $db, int $instructorId, string $anchor): array
    {
        $stmt = $db->prepare(
            'SELECT b.*, u.name AS client_name, p.name AS package_name
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.instructor_id = :instructor_id AND b.slot_date = :date
               AND b.status IN (\'awaiting_trainer_approval\',\'confirmed\',\'completed\')
             ORDER BY b.start_time ASC'
        );
        $stmt->execute(['instructor_id' => $instructorId, 'date' => $anchor]);

        return [
            'date' => $anchor,
            'bookings' => $stmt->fetchAll(),
            'prevAnchor' => date('Y-m-d', strtotime($anchor . ' -1 day')),
            'nextAnchor' => date('Y-m-d', strtotime($anchor . ' +1 day')),
        ];
    }

    public function saveAvailability(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $instructorId = (int) $instructor['id'];
        $windows = $request->input('windows', []);

        if (!is_array($windows)) {
            $this->fail('Invalid availability data.', 422);
        }

        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM instructor_availability WHERE instructor_id = :id AND is_recurring = 1');
        $stmt->execute(['id' => $instructorId]);

        foreach ($windows as $window) {
            $dayOfWeek = $window['day_of_week'] ?? null;
            $startTime = $window['start_time'] ?? '';
            $endTime = $window['end_time'] ?? '';

            if ($dayOfWeek === null || $startTime === '' || $endTime === '') {
                continue;
            }

            InstructorAvailability::insert([
                'instructor_id' => $instructorId,
                'day_of_week' => (int) $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_recurring' => 1,
            ]);
        }

        ActivityLog::log(Auth::id(), 'availability_updated', 'instructor', $instructorId);

        $this->success(null, 'Weekly availability updated.');
    }

    public function blockDate(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $date = (string) $request->input('blocked_date', '');

        if ($date === '') {
            $this->fail('Please choose a date to block.', 422);
        }

        $id = InstructorBlockedDate::insert([
            'instructor_id' => (int) $instructor['id'],
            'blocked_date' => $date,
            'reason' => $request->input('reason') ?: null,
        ]);

        ActivityLog::log(Auth::id(), 'date_blocked', 'instructor', (int) $instructor['id'], ['date' => $date]);

        $this->success(['id' => $id], 'Date blocked.');
    }

    public function unblockDate(Request $request, int $id): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $blocked = InstructorBlockedDate::find($id);

        if (!$blocked || (int) ($blocked['instructor_id'] ?? 0) !== (int) $instructor['id']) {
            $this->fail('Blocked date not found.', 404);
        }

        InstructorBlockedDate::delete($id);
        ActivityLog::log(Auth::id(), 'date_unblocked', 'instructor', (int) $instructor['id']);

        $this->success(null, 'Date unblocked.');
    }
}
