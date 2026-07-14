<?php

use App\Core\View;

$pageTitle = 'Booking Confirmed — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'bookings';

$dateLabel = date('l, d M Y', strtotime($booking['slot_date']));
$timeLabel = date('g:i A', strtotime($booking['start_time'])) . ' – ' . date('g:i A', strtotime($booking['end_time']));
$awaitingApproval = $booking['status'] === 'awaiting_trainer_approval';
?>
<div class="container booking-page">
    <div class="booking-success">
        <div class="booking-success__icon">
            <?= View::icon('check', 'icon', 28) ?>
        </div>
        <h1>Payment successful</h1>
        <?php if ($awaitingApproval): ?>
        <p class="text-muted">We've received your payment. <?= View::e($instructor['name'] ?? 'Your instructor') ?> still needs to confirm this session for <?= View::e($dateLabel) ?> at <?= View::e($timeLabel) ?> — you'll get an email as soon as they do.</p>
        <span class="badge badge-pending">Awaiting trainer confirmation</span>
        <?php else: ?>
        <p class="text-muted">Your session with <?= View::e($instructor['name'] ?? 'your instructor') ?> is confirmed for <?= View::e($dateLabel) ?> at <?= View::e($timeLabel) ?>.</p>
        <span class="badge badge-confirmed">Confirmed</span>
        <?php endif; ?>
    </div>

    <div class="booking-summary" style="max-width:560px;margin-inline:auto;">
        <div class="booking-summary__title">Booking reference</div>
        <p style="font-size:var(--font-size-lg);font-weight:var(--font-weight-semibold)"><?= View::e($booking['booking_ref']) ?></p>
        <p class="text-muted"><?= View::e($package['name']) ?> · <?= View::e($booking['currency']) ?> <?= number_format((float) $booking['total_amount'], 2) ?></p>

        <div class="booking-summary__cta flex flex-col gap-3">
            <a href="/booking/<?= View::e($booking['booking_ref']) ?>/invoice" class="btn btn-secondary btn-block" target="_blank" rel="noopener">View invoice</a>
            <a href="/dashboard/bookings" class="btn btn-primary btn-block">Go to my bookings</a>
        </div>
    </div>
</div>
<?php if (!$awaitingApproval): ?>
<script type="module">
import { fireConfetti } from '/assets/js/modules/confetti.js';
fireConfetti();
</script>
<?php endif; ?>
