<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Instructor;
use App\Models\MeetingLink;
use App\Models\Package;
use App\Models\SessionFeedback;
use App\Models\SessionResource;
use App\Models\User;

final class SessionController extends Controller
{
    private const MAX_RESOURCE_BYTES = 10 * 1024 * 1024;
    private const ALLOWED_RESOURCE_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'mp4'];

    public function show(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);

        $this->view('trainer/session', [
            'booking' => $booking,
            'client' => User::find((int) $booking['client_id']),
            'package' => Package::find($booking['package_id']),
            'meetingLink' => MeetingLink::forBooking((int) $booking['id']),
            'feedback' => SessionFeedback::forBooking((int) $booking['id']),
            'resources' => SessionResource::forBooking((int) $booking['id']),
        ], 'dashboard');
    }

    public function save(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);
        $instructor = Instructor::findByUserId(Auth::id());

        $data = [
            'booking_id' => (int) $booking['id'],
            'client_id' => (int) $booking['client_id'],
            'instructor_id' => (int) $instructor['id'],
            'session_notes' => trim((string) $request->input('session_notes', '')) ?: null,
            'recommendation' => trim((string) $request->input('recommendation', '')) ?: null,
            'homework' => trim((string) $request->input('homework', '')) ?: null,
            'attendance' => in_array($request->input('attendance'), ['present', 'absent'], true) ? $request->input('attendance') : null,
            'rating' => $request->input('rating') !== null && $request->input('rating') !== '' ? max(1, min(5, (int) $request->input('rating'))) : null,
            'feedback_text' => trim((string) $request->input('feedback_text', '')) ?: null,
        ];

        $existing = SessionFeedback::forBooking((int) $booking['id']);

        if ($existing) {
            SessionFeedback::update($existing['id'], $data);
        } else {
            SessionFeedback::insert($data);
        }

        if ($booking['status'] === 'confirmed' && strtotime($booking['slot_date'] . ' ' . $booking['end_time']) < time()) {
            Booking::update($booking['id'], ['status' => 'completed']);
        }

        ActivityLog::log(Auth::id(), 'session_notes_saved', 'booking', (int) $booking['id']);

        $this->success(null, 'Session updated.');
    }

    public function uploadResource(Request $request, string $ref): void
    {
        $booking = $this->ownedBooking($ref);
        $instructor = Instructor::findByUserId(Auth::id());

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a file to upload.', 422);
        }

        $file = $_FILES['file'];

        if ($file['size'] > self::MAX_RESOURCE_BYTES) {
            $this->fail('File must be smaller than 10MB.', 422);
        }

        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_RESOURCE_EXTENSIONS, true)) {
            $this->fail('Unsupported file type.', 422);
        }

        $dir = dirname(__DIR__, 3) . '/public/uploads/session-resources';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $booking['id'] . '-' . time() . '.' . $extension;
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded file. Please try again.', 500);
        }

        $title = trim((string) $request->input('title', '')) ?: pathinfo($file['name'], PATHINFO_FILENAME);

        $id = SessionResource::insert([
            'booking_id' => (int) $booking['id'],
            'instructor_id' => (int) $instructor['id'],
            'title' => $title,
            'file_path' => '/uploads/session-resources/' . $filename,
        ]);

        ActivityLog::log(Auth::id(), 'session_resource_uploaded', 'booking', (int) $booking['id']);

        $this->success(['id' => $id], 'Resource uploaded.');
    }

    private function ownedBooking(string $ref): array
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $booking = Booking::findByRef($ref);

        if (!$booking || (int) $booking['instructor_id'] !== (int) $instructor['id']) {
            $this->fail('Session not found.', 404);
        }

        if (!in_array($booking['status'], ['confirmed', 'completed'], true)) {
            $this->fail('This session is not confirmed yet.', 422);
        }

        return $booking;
    }
}
