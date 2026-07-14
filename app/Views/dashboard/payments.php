<?php

use App\Core\View;

$pageTitle = 'Payment History — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'payments';

$statusMeta = [
    'pending' => ['badge-pending', 'Pending'],
    'success' => ['badge-confirmed', 'Success'],
    'failed' => ['badge-cancelled', 'Failed'],
    'refunded' => ['badge-neutral', 'Refunded'],
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">Payment History</h1>
            <p class="services-hero__subtitle" style="margin:0">Every payment attempt across your bookings.</p>
        </div>
        <a href="/dashboard/bookings" class="btn btn-secondary btn-sm">My Bookings</a>
    </div>

    <?php if (empty($payments)): ?>
    <p class="text-muted text-center">No payments yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:720px;margin-inline:auto">
        <?php foreach ($payments as $payment):
            [$badgeClass, $badgeLabel] = $statusMeta[$payment['status']] ?? ['badge-neutral', ucfirst($payment['status'])];
        ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-2">
                <div style="font-weight:var(--font-weight-semibold)"><?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?></div>
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm)">
                <?= View::e(ucfirst($payment['gateway'])) ?> · Booking <?= View::e($payment['booking_ref']) ?> · <?= View::e(date('d M Y, g:i A', strtotime($payment['created_at']))) ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
