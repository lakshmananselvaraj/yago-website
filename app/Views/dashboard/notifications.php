<?php

use App\Core\View;

$pageTitle = 'Notifications — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'notifications';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="margin:0 0 var(--space-6);text-align:left">
        <h1 class="services-hero__title" style="margin:0">Notifications</h1>
        <p class="services-hero__subtitle" style="margin:0">Booking confirmations, session reminders, and payment updates.</p>
    </div>

    <?php if (empty($notifications)): ?>
    <p class="text-muted text-center">No notifications yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-3" style="max-width:720px;margin-inline:auto">
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
