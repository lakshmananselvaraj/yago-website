<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Instructor;

final class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $instructorRow = Instructor::findByUserId(Auth::id());
        $instructorId = (int) $instructorRow['id'];
        $instructor = Instructor::findWithName($instructorId);
        $db = Database::connection();

        $todaysClasses = $this->bookingList($db, $instructorId, "b.slot_date = CURDATE() AND b.status IN ('confirmed','completed')", 'b.start_time ASC');
        $upcoming = $this->bookingList($db, $instructorId, "b.slot_date > CURDATE() AND b.status = 'confirmed'", 'b.slot_date ASC, b.start_time ASC', 10);
        $awaitingApproval = $this->bookingList($db, $instructorId, "b.status = 'awaiting_trainer_approval'", 'b.slot_date ASC, b.start_time ASC');
        $completedCount = $this->countWhere($db, $instructorId, "b.status = 'completed'");
        $totalStudents = $this->countDistinctClients($db, $instructorId);

        $earningsStmt = $db->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) r FROM bookings
             WHERE instructor_id = :id AND status IN ('awaiting_trainer_approval','confirmed','completed')"
        );
        $earningsStmt->execute(['id' => $instructorId]);
        $totalEarnings = (float) $earningsStmt->fetch()['r'];

        $this->view('trainer/dashboard', [
            'instructor' => $instructor,
            'todaysClasses' => $todaysClasses,
            'upcoming' => $upcoming,
            'awaitingApproval' => $awaitingApproval,
            'completedCount' => $completedCount,
            'totalStudents' => $totalStudents,
            'totalEarnings' => $totalEarnings,
        ], 'dashboard');
    }

    private function bookingList(\PDO $db, int $instructorId, string $where, string $orderBy, int $limit = 50): array
    {
        $sql = "SELECT b.*, u.name AS client_name, p.name AS package_name
                FROM bookings b
                INNER JOIN users u ON u.id = b.client_id
                INNER JOIN packages p ON p.id = b.package_id
                WHERE b.instructor_id = :instructor_id AND {$where}
                ORDER BY {$orderBy}
                LIMIT " . max(1, $limit);

        $stmt = $db->prepare($sql);
        $stmt->execute(['instructor_id' => $instructorId]);

        return $stmt->fetchAll();
    }

    private function countWhere(\PDO $db, int $instructorId, string $where): int
    {
        $stmt = $db->prepare("SELECT COUNT(*) c FROM bookings b WHERE b.instructor_id = :instructor_id AND {$where}");
        $stmt->execute(['instructor_id' => $instructorId]);

        return (int) $stmt->fetch()['c'];
    }

    private function countDistinctClients(\PDO $db, int $instructorId): int
    {
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT client_id) c FROM bookings
             WHERE instructor_id = :id AND status IN ('confirmed','completed')"
        );
        $stmt->execute(['id' => $instructorId]);

        return (int) $stmt->fetch()['c'];
    }
}
