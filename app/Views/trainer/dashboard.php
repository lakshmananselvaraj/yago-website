<?php

use App\Core\View;

$pageTitle = 'Trainer Dashboard — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'dashboard';

$renderRow = static function (array $booking): void {
    $timeLabel = date('g:i A', strtotime($booking['start_time']));
    $dateLabel = date('d M', strtotime($booking['slot_date']));
    ?>
    <div class="trainer-schedule-row">
        <div class="flex items-center gap-4">
            <span class="trainer-schedule-row__time"><?= View::e($dateLabel) ?> · <?= View::e($timeLabel) ?></span>
            <div>
                <strong><?= View::e($booking['client_name']) ?></strong>
                <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package_name']) ?></span>
            </div>
        </div>
        <a href="/trainer/sessions/<?= View::e($booking['booking_ref']) ?>" class="btn btn-secondary btn-sm">View</a>
    </div>
    <?php
};
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Welcome back, <?= View::e(explode(' ', $instructor['name'] ?? '')[0] ?? 'Trainer') ?></h1>
            <p class="trainer-hero__subtitle">Here's what's on your schedule.</p>
        </div>
    </div>

    <div class="trainer-stat-row">
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= count($todaysClasses) ?></div>
            <div class="trainer-stat__label">Today's classes</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= count($awaitingApproval) ?></div>
            <div class="trainer-stat__label">Awaiting your approval</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= (int) $totalStudents ?></div>
            <div class="trainer-stat__label">Students</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= (int) $completedCount ?></div>
            <div class="trainer-stat__label">Completed sessions</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value">₹<?= number_format($totalEarnings, 0) ?></div>
            <div class="trainer-stat__label">Total earnings</div>
        </div>
    </div>

    <?php if (!empty($awaitingApproval)): ?>
    <h2 class="trainer-section-title">Awaiting Your Approval</h2>
    <div class="trainer-schedule">
        <?php foreach ($awaitingApproval as $booking): ?>
        <div class="trainer-schedule-row">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('d M', strtotime($booking['slot_date']))) ?> · <?= View::e(date('g:i A', strtotime($booking['start_time']))) ?></span>
                <div>
                    <strong><?= View::e($booking['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package_name']) ?></span>
                </div>
            </div>
            <a href="/trainer/bookings" class="btn btn-primary btn-sm">Review</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Today's Classes</h2>
    <?php if (empty($todaysClasses)): ?>
    <p class="text-muted mb-8">Nothing scheduled for today.</p>
    <?php else: ?>
    <div class="trainer-schedule">
        <?php foreach ($todaysClasses as $booking) {
            $renderRow($booking);
        } ?>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Upcoming Sessions</h2>
    <?php if (empty($upcoming)): ?>
    <p class="text-muted">No upcoming confirmed sessions.</p>
    <?php else: ?>
    <div class="trainer-schedule">
        <?php foreach ($upcoming as $booking) {
            $renderRow($booking);
        } ?>
    </div>
    <?php endif; ?>
</div>
