<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\ActivityLog;
use PDO;

final class DashboardController extends Controller
{
    private const SERIES_DAYS = 30;

    public function activity(Request $request): void
    {
        $this->view('admin/activity', [
            'logs' => ActivityLog::recent(100),
        ], 'dashboard');
    }

    public function index(Request $request): void
    {
        $db = Database::connection();

        $todayCount = (int) $db->query("SELECT COUNT(*) c FROM bookings WHERE slot_date = CURDATE()")->fetch()['c'];
        $confirmedCount = (int) $db->query("SELECT COUNT(*) c FROM bookings WHERE status = 'confirmed'")->fetch()['c'];
        $pendingCount = (int) $db->query("SELECT COUNT(*) c FROM bookings WHERE status = 'pending_payment'")->fetch()['c'];
        $revenue = (float) $db->query("SELECT COALESCE(SUM(total_amount), 0) r FROM bookings WHERE status IN ('awaiting_trainer_approval', 'confirmed', 'completed')")->fetch()['r'];

        $totalClients = (int) $db->query("SELECT COUNT(*) c FROM users WHERE role = 'client'")->fetch()['c'];
        $totalTrainers = (int) $db->query("SELECT COUNT(*) c FROM instructors WHERE status = 'active'")->fetch()['c'];
        $activePackages = (int) $db->query("SELECT COUNT(*) c FROM packages WHERE is_active = 1")->fetch()['c'];
        $pendingPaymentsAmount = (float) $db->query("SELECT COALESCE(SUM(total_amount), 0) r FROM bookings WHERE status = 'pending_payment'")->fetch()['r'];

        $todayRevenue = (float) $db->query(
            "SELECT COALESCE(SUM(amount), 0) r FROM payments WHERE status = 'success' AND DATE(created_at) = CURDATE()"
        )->fetch()['r'];
        $monthRevenue = (float) $db->query(
            "SELECT COALESCE(SUM(amount), 0) r FROM payments WHERE status = 'success' AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        )->fetch()['r'];

        $paymentsByGateway = $db->query(
            "SELECT gateway AS label, COUNT(*) AS value FROM payments WHERE status = 'success' GROUP BY gateway ORDER BY value DESC"
        )->fetchAll();
        $paymentsByStatus = $db->query(
            "SELECT status AS label, COUNT(*) AS value FROM payments GROUP BY status ORDER BY value DESC"
        )->fetchAll();

        $revenueSeries = $this->dailySeries($db, "SELECT DATE(created_at) d, COALESCE(SUM(total_amount),0) v FROM bookings WHERE status IN ('awaiting_trainer_approval','confirmed','completed') AND created_at >= :since GROUP BY DATE(created_at)");
        $bookingsSeries = $this->dailySeries($db, "SELECT DATE(created_at) d, COUNT(*) v FROM bookings WHERE created_at >= :since GROUP BY DATE(created_at)");
        $userGrowthSeries = $this->dailySeries($db, "SELECT DATE(created_at) d, COUNT(*) v FROM users WHERE role = 'client' AND created_at >= :since GROUP BY DATE(created_at)");

        $packageSales = $db->query(
            "SELECT p.name AS label, COUNT(*) AS value
             FROM bookings b INNER JOIN packages p ON p.id = b.package_id
             WHERE b.status IN ('awaiting_trainer_approval','confirmed','completed')
             GROUP BY p.id, p.name ORDER BY value DESC LIMIT 6"
        )->fetchAll();

        $popularInstructors = $db->query(
            "SELECT u.name AS label, COUNT(*) AS value
             FROM bookings b
             INNER JOIN instructors i ON i.id = b.instructor_id
             INNER JOIN users u ON u.id = i.user_id
             WHERE b.status IN ('awaiting_trainer_approval','confirmed','completed')
             GROUP BY i.id, u.name ORDER BY value DESC LIMIT 6"
        )->fetchAll();

        $todaysSchedule = $this->bookingList($db, "b.slot_date = CURDATE() AND b.status IN ('confirmed','completed')", 'b.start_time ASC');
        $upcoming = $this->bookingList($db, "b.slot_date > CURDATE() AND b.status = 'confirmed'", 'b.slot_date ASC, b.start_time ASC', 10);
        $cancelled = $this->bookingList($db, "b.status = 'cancelled'", 'b.updated_at DESC', 10);

        $this->view('admin/dashboard', [
            'todayCount' => $todayCount,
            'confirmedCount' => $confirmedCount,
            'pendingCount' => $pendingCount,
            'revenue' => $revenue,
            'totalClients' => $totalClients,
            'totalTrainers' => $totalTrainers,
            'activePackages' => $activePackages,
            'pendingPaymentsAmount' => $pendingPaymentsAmount,
            'todayRevenue' => $todayRevenue,
            'monthRevenue' => $monthRevenue,
            'paymentsByGateway' => $paymentsByGateway,
            'paymentsByStatus' => $paymentsByStatus,
            'revenueSeries' => $revenueSeries,
            'bookingsSeries' => $bookingsSeries,
            'userGrowthSeries' => $userGrowthSeries,
            'packageSales' => $packageSales,
            'popularInstructors' => $popularInstructors,
            'todaysSchedule' => $todaysSchedule,
            'upcoming' => $upcoming,
            'cancelled' => $cancelled,
        ], 'dashboard');
    }

    private function dailySeries(PDO $db, string $sql): array
    {
        $since = date('Y-m-d', strtotime('-' . (self::SERIES_DAYS - 1) . ' days'));
        $stmt = $db->prepare($sql);
        $stmt->execute(['since' => $since]);

        $byDate = [];
        foreach ($stmt->fetchAll() as $row) {
            $byDate[$row['d']] = (float) $row['v'];
        }

        $labels = [];
        $values = [];
        for ($i = self::SERIES_DAYS - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));
            $values[] = $byDate[$date] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function bookingList(PDO $db, string $where, string $orderBy, int $limit = 50): array
    {
        $sql = "SELECT b.*, u.name AS client_name, iu.name AS instructor_name, p.name AS package_name
                FROM bookings b
                INNER JOIN users u ON u.id = b.client_id
                INNER JOIN instructors i ON i.id = b.instructor_id
                INNER JOIN users iu ON iu.id = i.user_id
                INNER JOIN packages p ON p.id = b.package_id
                WHERE {$where}
                ORDER BY {$orderBy}
                LIMIT " . max(1, $limit);

        return $db->query($sql)->fetchAll();
    }
}
