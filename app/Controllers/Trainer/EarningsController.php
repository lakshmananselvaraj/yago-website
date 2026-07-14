<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Instructor;

final class EarningsController extends Controller
{
    private const SERIES_DAYS = 30;

    public function index(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $instructorId = (int) $instructor['id'];
        $db = Database::connection();

        $totalStmt = $db->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) r, COUNT(*) c FROM bookings
             WHERE instructor_id = :id AND status IN ('awaiting_trainer_approval','confirmed','completed')"
        );
        $totalStmt->execute(['id' => $instructorId]);
        $totals = $totalStmt->fetch();

        $monthStmt = $db->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) r, COUNT(*) c FROM bookings
             WHERE instructor_id = :id AND status IN ('awaiting_trainer_approval','confirmed','completed')
               AND created_at >= :since"
        );
        $monthStmt->execute(['id' => $instructorId, 'since' => date('Y-m-d', strtotime('-30 days'))]);
        $thisMonth = $monthStmt->fetch();

        $historyStmt = $db->prepare(
            "SELECT b.booking_ref, b.slot_date, b.total_amount, b.status, u.name AS client_name, p.name AS package_name
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.instructor_id = :id AND b.status IN ('awaiting_trainer_approval','confirmed','completed')
             ORDER BY b.slot_date DESC
             LIMIT 100"
        );
        $historyStmt->execute(['id' => $instructorId]);

        $this->view('trainer/earnings', [
            'totalRevenue' => (float) $totals['r'],
            'totalSessions' => (int) $totals['c'],
            'monthRevenue' => (float) $thisMonth['r'],
            'monthSessions' => (int) $thisMonth['c'],
            'history' => $historyStmt->fetchAll(),
            'ratingAvg' => (float) ($instructor['rating_avg'] ?? 0),
            'ratingCount' => (int) ($instructor['rating_count'] ?? 0),
        ], 'dashboard');
    }
}
