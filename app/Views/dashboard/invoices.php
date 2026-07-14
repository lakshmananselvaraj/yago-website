<?php

use App\Core\View;
?>
<?php $pageTitle = 'Invoices — Vipasa Yoga'; $pageCss = 'booking'; $portal = 'client'; $active = 'invoices'; ?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">Invoices</h1>
            <p class="services-hero__subtitle" style="margin:0">View or print the invoice for any confirmed booking.</p>
        </div>
        <a href="/dashboard/bookings" class="btn btn-secondary btn-sm">My Bookings</a>
    </div>

    <?php if (empty($bookings)): ?>
    <p class="text-muted text-center">No invoices yet — invoices become available once a booking is confirmed.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:720px;margin-inline:auto">
        <?php foreach ($bookings as $booking):
            $dateLabel = date('d M Y', strtotime($booking['slot_date']));
        ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-2">
                <div style="font-weight:var(--font-weight-semibold)"><?= View::e($booking['package']['name'] ?? 'Package') ?></div>
                <a href="/booking/<?= View::e($booking['booking_ref']) ?>/invoice" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">View / print</a>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm)">
                With <?= View::e($booking['instructor']['name'] ?? 'Instructor') ?> · <?= View::e($dateLabel) ?>
                · Ref <?= View::e($booking['booking_ref']) ?> · <?= View::e($booking['currency']) ?> <?= number_format((float) $booking['total_amount'], 2) ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
