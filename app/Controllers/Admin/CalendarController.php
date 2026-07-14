<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class CalendarController extends Controller
{
    public function index(Request $request): void
    {
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

        $sql = 'SELECT b.*, u.name AS client_name, iu.name AS instructor_name, p.name AS package_name
                FROM bookings b
                INNER JOIN users u ON u.id = b.client_id
                INNER JOIN instructors i ON i.id = b.instructor_id
                INNER JOIN users iu ON iu.id = i.user_id
                INNER JOIN packages p ON p.id = b.package_id
                WHERE b.slot_date BETWEEN :first_day AND :last_day
                ORDER BY b.slot_date ASC, b.start_time ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute(['first_day' => $firstDay, 'last_day' => $lastDay]);
        $bookings = $stmt->fetchAll();

        $byDate = [];
        foreach ($bookings as $booking) {
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

        $this->view('admin/calendar', [
            'byDate' => $byDate,
            'month' => $month,
            'year' => $year,
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
        ], 'dashboard');
    }
}
