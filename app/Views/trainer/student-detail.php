<?php

use App\Core\View;

$pageTitle = View::e($client['name']) . ' — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'students';

$statusMeta = [
    'awaiting_trainer_approval' => ['badge-pending', 'Awaiting you'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
    'cancelled' => ['badge-cancelled', 'Cancelled'],
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title"><?= View::e($client['name']) ?></h1>
            <p class="trainer-hero__subtitle"><?= View::e($client['email'] ?? '') ?><?= $client['phone'] ? ' · ' . View::e($client['phone']) : '' ?></p>
        </div>
    </div>

    <?php if ($profile): ?>
    <div class="card mb-8" style="padding:var(--space-6)">
        <h2 class="trainer-section-title" style="margin-bottom:var(--space-3)">Profile</h2>
        <p class="text-muted" style="margin-bottom:var(--space-2)">
            <?php if ($profile['age']): ?><?= (int) $profile['age'] ?> yrs · <?php endif; ?>
            <?php if ($profile['gender']): ?><?= View::e(str_replace('_', ' ', $profile['gender'])) ?> · <?php endif; ?>
            <?= View::e($profile['country'] ?? '') ?>
        </p>
        <?php if (!empty($profile['bio'])): ?>
        <p style="margin-bottom:var(--space-3)"><?= View::e($profile['bio']) ?></p>
        <?php endif; ?>
        <?php if (!empty($profile['medical_notes'])): ?>
        <div class="form-group__error" style="background:var(--color-warning-soft);padding:var(--space-4);border-radius:var(--radius-md)">
            <strong>Medical notes:</strong> <?= View::e($profile['medical_notes']) ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Session History</h2>
    <div class="trainer-schedule mb-8">
        <?php foreach ($history as $booking):
            [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
        ?>
        <div class="trainer-schedule-row">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('d M Y', strtotime($booking['slot_date']))) ?></span>
                <div>
                    <strong><?= View::e($booking['package_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · Ref <?= View::e($booking['booking_ref']) ?></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
                <?php if (in_array($booking['status'], ['confirmed', 'completed'], true)): ?>
                <a href="/trainer/sessions/<?= View::e($booking['booking_ref']) ?>" class="btn btn-secondary btn-sm">Session</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($feedback)): ?>
    <h2 class="trainer-section-title">Your Notes</h2>
    <div class="flex flex-col gap-3">
        <?php foreach ($feedback as $entry): ?>
        <div class="card" style="padding:var(--space-5)">
            <p class="text-muted" style="font-size:var(--font-size-sm);margin-bottom:var(--space-2)"><?= View::e(date('d M Y', strtotime($entry['created_at']))) ?><?= $entry['attendance'] ? ' · ' . ucfirst($entry['attendance']) : '' ?><?= $entry['rating'] ? ' · Rated ' . (int) $entry['rating'] . '/5' : '' ?></p>
            <?php if (!empty($entry['session_notes'])): ?><p><?= View::e($entry['session_notes']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
