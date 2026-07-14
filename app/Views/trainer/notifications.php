<?php

use App\Core\View;

$pageTitle = 'Notifications — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'notifications';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Notifications</h1>
            <p class="trainer-hero__subtitle">Updates about your bookings and account.</p>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
    <p class="text-muted text-center">No notifications yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-3">
        <?php foreach ($notifications as $notification): ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-1">
                <strong><?= View::e($notification['title']) ?></strong>
                <span class="text-muted" style="font-size:var(--font-size-sm)"><?= View::e(date('d M Y, g:i A', strtotime($notification['created_at']))) ?></span>
            </div>
            <?php if (!empty($notification['body'])): ?>
            <p class="text-muted" style="margin:0"><?= View::e($notification['body']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
