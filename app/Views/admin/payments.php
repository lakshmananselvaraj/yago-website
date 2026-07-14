<?php

use App\Core\View;

$pageTitle = 'Manage Payments — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'admin';
$active = 'payments';

$statuses = ['pending', 'success', 'failed', 'refunded'];
$statusMeta = [
    'pending' => ['badge-pending', 'Pending'],
    'success' => ['badge-confirmed', 'Success'],
    'failed' => ['badge-cancelled', 'Failed'],
    'refunded' => ['badge-neutral', 'Refunded'],
];

$gateways = ['razorpay', 'stripe', 'paypal', 'wallet', 'other'];
$gatewayLabels = [
    'razorpay' => 'Razorpay',
    'stripe' => 'Stripe',
    'paypal' => 'PayPal',
    'wallet' => 'Wallet',
    'other' => 'Other',
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero">
        <h1 class="services-hero__title">Manage Payments</h1>
        <p class="services-hero__subtitle">Review payment transactions across all bookings.</p>
    </div>

    <div class="services-filters">
        <a href="/admin/payments<?= $selectedGateway ? '?gateway=' . urlencode($selectedGateway) : '' ?>" class="chip<?= !$selectedStatus ? ' is-selected' : '' ?>">All statuses</a>
        <?php foreach ($statuses as $status): ?>
        <a href="/admin/payments?status=<?= urlencode($status) ?><?= $selectedGateway ? '&gateway=' . urlencode($selectedGateway) : '' ?>" class="chip<?= $selectedStatus === $status ? ' is-selected' : '' ?>"><?= View::e($statusMeta[$status][1]) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="services-filters">
        <a href="/admin/payments<?= $selectedStatus ? '?status=' . urlencode($selectedStatus) : '' ?>" class="chip<?= !$selectedGateway ? ' is-selected' : '' ?>">All gateways</a>
        <?php foreach ($gateways as $gateway): ?>
        <a href="/admin/payments?gateway=<?= urlencode($gateway) ?><?= $selectedStatus ? '&status=' . urlencode($selectedStatus) : '' ?>" class="chip<?= $selectedGateway === $gateway ? ' is-selected' : '' ?>"><?= View::e($gatewayLabels[$gateway]) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($payments)): ?>
    <p class="text-muted text-center">No payments match this filter.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:820px;margin-inline:auto">
        <?php foreach ($payments as $payment):
            [$badgeClass, $badgeLabel] = $statusMeta[$payment['status']] ?? ['badge-pending', ucfirst($payment['status'])];
            $dateLabel = date('d M Y, g:i A', strtotime($payment['created_at']));
        ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-2">
                <div style="font-weight:var(--font-weight-semibold)"><?= View::e($payment['client_name']) ?> <span class="text-muted" style="font-weight:var(--font-weight-regular)">(<?= View::e($payment['client_email']) ?>)</span></div>
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm);margin-bottom:var(--space-4)">
                Booking Ref <?= View::e($payment['booking_ref']) ?> · <?= View::e($payment['currency']) ?> <?= number_format((float) $payment['amount'], 2) ?>
                <span class="chip" style="margin-inline-start:var(--space-2)"><?= View::e($gatewayLabels[$payment['gateway']] ?? ucfirst($payment['gateway'])) ?></span>
                <?php if (!empty($payment['gateway_txn_id'])): ?>
                · Txn <?= View::e($payment['gateway_txn_id']) ?>
                <?php endif; ?>
                · <?= View::e($dateLabel) ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-3 mt-8">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="/admin/payments?page=<?= $p ?><?= $selectedStatus ? '&status=' . urlencode($selectedStatus) : '' ?><?= $selectedGateway ? '&gateway=' . urlencode($selectedGateway) : '' ?>" class="chip<?= $p === $page ? ' is-selected' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
