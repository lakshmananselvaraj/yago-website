<?php

declare(strict_types=1);

/**
 * Standalone CLI entry point — sends "Upcoming Session Reminder" emails for
 * confirmed bookings starting within the next 24 hours that haven't already
 * had a reminder sent. Intended to be invoked on a schedule (Windows Task
 * Scheduler locally, cron/cPanel "Cron Jobs" in production) — see README.md
 * for the exact commands. Safe to run as often as you like: each booking is
 * only ever reminded once (see UPDATE ... WHERE reminder_sent_at IS NULL).
 *
 * Usage: php bin/send-reminders.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Mailer;
use App\Core\View;
use App\Models\Instructor;
use App\Models\Package;
use App\Models\User;

$db = Database::connection();

$stmt = $db->query(
    "SELECT * FROM bookings
     WHERE status = 'confirmed'
       AND reminder_sent_at IS NULL
       AND TIMESTAMP(slot_date, start_time) BETWEEN NOW() AND NOW() + INTERVAL 24 HOUR"
);
$bookings = $stmt->fetchAll();

$sent = 0;

foreach ($bookings as $booking) {
    $client = User::find((int) $booking['client_id']);

    if (!$client || !$client['email']) {
        continue;
    }

    $instructor = Instructor::findWithName((int) $booking['instructor_id']);
    $package = Package::find((int) $booking['package_id']);

    $clientName = View::e($client['name']);
    $ref = View::e($booking['booking_ref']);
    $packageName = View::e($package['name'] ?? 'Session');
    $instructorName = View::e($instructor['name'] ?? 'your instructor');
    $dateLabel = View::e(date('l, d M Y', strtotime($booking['slot_date'])));
    $timeLabel = View::e(date('g:i A', strtotime($booking['start_time'])));

    $sentOk = Mailer::send($client['email'], 'Upcoming session reminder — ' . $booking['booking_ref'], <<<HTML
        <p>Hi {$clientName},</p>
        <p>This is a reminder about your upcoming session:</p>
        <p><strong>{$packageName}</strong> with {$instructorName}<br>{$dateLabel} at {$timeLabel}</p>
        <p>Reference: {$ref}</p>
        HTML);

    if ($sentOk) {
        $update = $db->prepare('UPDATE bookings SET reminder_sent_at = NOW() WHERE id = :id');
        $update->execute(['id' => $booking['id']]);
        $sent++;
    }
}

echo "Reminders sent: {$sent} (of " . count($bookings) . " due)\n";
