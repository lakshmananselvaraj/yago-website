<?php

use App\Core\View;

$pageTitle = 'Payment Failed — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'services';
?>
<div class="container booking-page">
    <div class="booking-success">
        <div class="booking-success__icon" style="background-color:var(--color-error-soft);color:var(--color-error)">
            <?= View::icon('x', 'icon', 28) ?>
        </div>
        <h1>Payment failed</h1>
        <p class="text-muted">Your slot is still held — you can retry the payment or choose a different one.</p>
        <span class="badge badge-pending">Pending payment</span>
    </div>

    <div class="booking-summary" style="max-width:480px;margin-inline:auto;">
        <p class="text-muted">Reference <strong><?= View::e($booking['booking_ref']) ?></strong></p>
        <div class="booking-summary__cta flex flex-col gap-3">
            <a href="/booking/confirm/<?= View::e($booking['booking_ref']) ?>" class="btn btn-accent btn-block">Retry payment</a>
            <a href="/services" class="btn btn-secondary btn-block">Back to services</a>
        </div>
    </div>
</div>
