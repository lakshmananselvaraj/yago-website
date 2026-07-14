<?php

use App\Core\View;

$active = $active ?? '';
$adminNavLinks = [
    'dashboard' => ['/admin', 'Dashboard'],
    'bookings' => ['/admin/bookings', 'Bookings'],
    'calendar' => ['/admin/calendar', 'Calendar'],
    'packages' => ['/admin/packages', 'Packages'],
    'instructors' => ['/admin/instructors', 'Instructors'],
    'customers' => ['/admin/customers', 'Customers'],
    'payments' => ['/admin/payments', 'Payments'],
    'reports' => ['/admin/reports', 'Reports'],
    'settings' => ['/admin/settings', 'Settings'],
    'notifications' => ['/admin/notifications', 'Notifications'],
    'video-sessions' => ['/admin/video-sessions', 'Video Sessions'],
    'activity' => ['/admin/activity', 'Activity'],
];
?>
<div class="flex gap-2 flex-wrap mb-6" style="padding-bottom:var(--space-4);border-bottom:1px solid var(--border-subtle)">
    <?php foreach ($adminNavLinks as $key => [$href, $label]): ?>
    <a href="<?= View::e($href) ?>" class="btn btn-sm <?= $active === $key ? 'btn-primary' : 'btn-secondary' ?>"><?= View::e($label) ?></a>
    <?php endforeach; ?>
</div>
