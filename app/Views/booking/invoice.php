<?php

use App\Core\View;

$pageTitle = 'Invoice ' . $booking['booking_ref'] . ' — Vipasa Yoga';
$pageCss = 'booking';

$dateLabel = date('d M Y', strtotime($booking['slot_date']));
$timeLabel = date('g:i A', strtotime($booking['start_time'])) . ' – ' . date('g:i A', strtotime($booking['end_time']));
$issuedLabel = date('d M Y', strtotime($booking['updated_at']));
?>
<div class="container" style="max-width:720px;padding-block:var(--space-10)">
    <button type="button" class="btn btn-secondary no-print mb-6" onclick="window.print()">Print / Save as PDF</button>

    <div class="card" style="padding:var(--space-8)">
        <div class="flex items-center justify-between mb-8">
            <div class="splash__wordmark">Vipasa Yoga</div>
            <div class="text-right">
                <div style="font-weight:var(--font-weight-semibold)">Invoice</div>
                <div class="text-muted" style="font-size:var(--font-size-sm)"><?= View::e($booking['booking_ref']) ?></div>
                <div class="text-muted" style="font-size:var(--font-size-sm)">Issued <?= View::e($issuedLabel) ?></div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="form-group__hint" style="margin:0">Instructor</div>
                <div style="font-weight:var(--font-weight-medium)"><?= View::e($instructor['name'] ?? 'Yoga Instructor') ?></div>
            </div>
            <div class="text-right">
                <div class="form-group__hint" style="margin:0">Session</div>
                <div style="font-weight:var(--font-weight-medium)"><?= View::e($dateLabel) ?> · <?= View::e($timeLabel) ?></div>
            </div>
        </div>

        <table class="price-breakdown mb-6">
            <tbody>
                <tr>
                    <td><?= View::e($package['name']) ?></td>
                    <td><?= View::e($booking['currency']) ?> <?= number_format((float) $booking['price'], 2) ?></td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td><?= View::e($booking['currency']) ?> <?= number_format((float) $booking['tax_amount'], 2) ?></td>
                </tr>
                <?php if ((float) $booking['discount_amount'] > 0): ?>
                <tr class="is-discount">
                    <td>Discount</td>
                    <td>&minus; <?= View::e($booking['currency']) ?> <?= number_format((float) $booking['discount_amount'], 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="price-breakdown__total">
                    <td>Total paid</td>
                    <td><?= View::e($booking['currency']) ?> <?= number_format((float) $booking['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if ($payment): ?>
        <p class="text-muted" style="font-size:var(--font-size-sm)">Payment ID: <?= View::e($payment['gateway_txn_id'] ?? $payment['gateway_order_id'] ?? '—') ?></p>
        <?php endif; ?>

        <?php if ($meetingLink): ?>
        <p class="text-muted" style="font-size:var(--font-size-sm)">Session link: <?= View::e($meetingLink['url']) ?></p>
        <?php endif; ?>
    </div>
</div>
