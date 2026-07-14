<?php

use App\Core\View;

$pageTitle = 'Earnings — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'earnings';

$statusMeta = [
    'awaiting_trainer_approval' => ['badge-pending', 'Awaiting you'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Earnings &amp; Performance</h1>
            <p class="trainer-hero__subtitle">Based on your actual confirmed sessions.</p>
        </div>
    </div>

    <div class="trainer-stat-row">
        <div class="trainer-stat">
            <div class="trainer-stat__value">₹<?= number_format($totalRevenue, 2) ?></div>
            <div class="trainer-stat__label">Total earnings</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= $totalSessions ?></div>
            <div class="trainer-stat__label">Total sessions</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value">₹<?= number_format($monthRevenue, 2) ?></div>
            <div class="trainer-stat__label">Last 30 days</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= $ratingCount > 0 ? number_format($ratingAvg, 1) : 'New' ?></div>
            <div class="trainer-stat__label"><?= $ratingCount > 0 ? $ratingCount . ' review(s)' : 'No reviews yet' ?></div>
        </div>
    </div>

    <h2 class="trainer-section-title">Session History</h2>
    <?php if (empty($history)): ?>
    <p class="text-muted">No paid sessions yet.</p>
    <?php else: ?>
    <div class="trainer-schedule">
        <?php foreach ($history as $row):
            [$badgeClass, $badgeLabel] = $statusMeta[$row['status']] ?? ['badge-neutral', ucfirst($row['status'])];
        ?>
        <div class="trainer-schedule-row">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('d M Y', strtotime($row['slot_date']))) ?></span>
                <div>
                    <strong><?= View::e($row['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($row['package_name']) ?></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
                <strong>₹<?= number_format((float) $row['total_amount'], 2) ?></strong>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
