<?php

use App\Core\View;

$pageTitle = 'Activity Timeline — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'activity';

$actionLabels = [
    'signup' => 'signed up',
    'login' => 'logged in',
    'login_google' => 'logged in with Google',
    'booking_created' => 'created a booking',
    'payment_success' => 'completed a payment',
    'payment_failed' => 'had a payment fail',
    'meeting_link_attached' => 'attached a meeting link to a booking',
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16);max-width:760px">
    <div class="services-hero" style="text-align:left;margin:0 0 var(--space-8)">
        <h1 class="services-hero__title" style="margin:0">Activity Timeline</h1>
        <p class="services-hero__subtitle" style="margin:0">The last 100 events across the platform.</p>
    </div>

    <?php if (empty($logs)): ?>
    <p class="text-muted">No activity recorded yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-3">
        <?php foreach ($logs as $log): ?>
        <div class="card flex items-center justify-between flex-wrap gap-3" style="padding:var(--space-4) var(--space-5)">
            <div>
                <strong><?= View::e($log['user_name'] ?? 'System') ?></strong>
                <span class="text-muted"> <?= View::e($actionLabels[$log['action']] ?? str_replace('_', ' ', $log['action'])) ?></span>
                <?php if (!empty($log['entity_type']) && !empty($log['entity_id'])): ?>
                <span class="text-muted" style="font-size:var(--font-size-xs)">(<?= View::e($log['entity_type']) ?> #<?= (int) $log['entity_id'] ?>)</span>
                <?php endif; ?>
            </div>
            <span class="text-muted" style="font-size:var(--font-size-sm)"><?= date('d M Y, g:i A', strtotime($log['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
