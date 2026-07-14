<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\ClientProfile;
use App\Models\Instructor;
use App\Models\User;

final class StudentController extends Controller
{
    public function index(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $db = Database::connection();

        $stmt = $db->prepare(
            "SELECT u.id, u.name, u.email, u.phone, COUNT(*) AS session_count, MAX(b.slot_date) AS last_session
             FROM bookings b
             INNER JOIN users u ON u.id = b.client_id
             WHERE b.instructor_id = :id AND b.status IN ('confirmed','completed')
             GROUP BY u.id, u.name, u.email, u.phone
             ORDER BY last_session DESC"
        );
        $stmt->execute(['id' => (int) $instructor['id']]);

        $this->view('trainer/students', ['students' => $stmt->fetchAll()], 'dashboard');
    }

    public function show(Request $request, int $id): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $instructorId = (int) $instructor['id'];

        $db = Database::connection();
        $countStmt = $db->prepare(
            "SELECT COUNT(*) c FROM bookings WHERE instructor_id = :instructor_id AND client_id = :client_id AND status IN ('confirmed','completed')"
        );
        $countStmt->execute(['instructor_id' => $instructorId, 'client_id' => $id]);

        if ((int) $countStmt->fetch()['c'] === 0) {
            $this->fail('This student has no sessions with you.', 404);
        }

        $client = User::find($id);
        $profile = ClientProfile::findByUserId($id);

        $historyStmt = $db->prepare(
            "SELECT b.*, p.name AS package_name
             FROM bookings b
             INNER JOIN packages p ON p.id = b.package_id
             WHERE b.instructor_id = :instructor_id AND b.client_id = :client_id
             ORDER BY b.slot_date DESC, b.start_time DESC"
        );
        $historyStmt->execute(['instructor_id' => $instructorId, 'client_id' => $id]);

        $feedbackStmt = $db->prepare(
            'SELECT * FROM session_feedback WHERE instructor_id = :instructor_id AND client_id = :client_id ORDER BY created_at DESC'
        );
        $feedbackStmt->execute(['instructor_id' => $instructorId, 'client_id' => $id]);

        $this->view('trainer/student-detail', [
            'client' => $client,
            'profile' => $profile ? ClientProfile::hydrate($profile) : null,
            'history' => $historyStmt->fetchAll(),
            'feedback' => $feedbackStmt->fetchAll(),
        ], 'dashboard');
    }
}
