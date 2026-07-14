<?php

use App\Core\View;

/** @var string $portal */
/** @var string $active */

$navByPortal = [
    'admin' => [
        'dashboard' => ['/admin', 'Dashboard', 'home'],
        'bookings' => ['/admin/bookings', 'Bookings', 'calendar-week'],
        'calendar' => ['/admin/calendar', 'Calendar', 'calendar-month'],
        'packages' => ['/admin/packages', 'Packages', 'sliders'],
        'instructors' => ['/admin/instructors', 'Trainers', 'users'],
        'customers' => ['/admin/customers', 'Clients', 'user'],
        'payments' => ['/admin/payments', 'Payments', 'credit-card'],
        'reports' => ['/admin/reports', 'Reports', 'file-text'],
        'video-sessions' => ['/admin/video-sessions', 'Video Classes', 'image'],
        'content' => ['/admin/content', 'Website Content', 'file-text'],
        'gallery' => ['/admin/gallery', 'Gallery', 'image'],
        'notifications' => ['/admin/notifications', 'Notifications', 'bell'],
        'activity' => ['/admin/activity', 'Activity', 'clock'],
        'settings' => ['/admin/settings', 'Settings', 'settings'],
    ],
    'trainer' => [
        'dashboard' => ['/trainer/dashboard', 'Dashboard', 'home'],
        'bookings' => ['/trainer/bookings', 'Bookings', 'calendar-week'],
        'calendar' => ['/trainer/calendar', 'Calendar', 'calendar-month'],
        'students' => ['/trainer/students', 'Students', 'users'],
        'earnings' => ['/trainer/earnings', 'Earnings', 'wallet'],
        'reviews' => ['/trainer/reviews', 'Reviews', 'star'],
        'notifications' => ['/trainer/notifications', 'Notifications', 'bell'],
        'profile' => ['/trainer/profile', 'Profile', 'user'],
    ],
    'client' => [
        'dashboard' => ['/dashboard', 'Dashboard', 'home'],
        'services' => ['/services', 'Book a Session', 'calendar-week'],
        'instructors' => ['/instructors', 'Instructors', 'users'],
        'bookings' => ['/dashboard/bookings', 'My Bookings', 'calendar-month'],
        'favorites' => ['/dashboard/favorites', 'Favorites', 'heart'],
        'payments' => ['/dashboard/payments', 'Payments', 'credit-card'],
        'invoices' => ['/dashboard/invoices', 'Invoices', 'file-text'],
        'notifications' => ['/dashboard/notifications', 'Notifications', 'bell'],
        'profile' => ['/onboarding/profile', 'Profile', 'user'],
    ],
];

$navLinks = $navByPortal[$portal] ?? [];
$brandLabel = match ($portal) {
    'admin' => 'Vipasa Admin',
    'trainer' => 'Vipasa Teach',
    default => 'Vipasa Yoga',
};
?>
<aside class="dash-sidebar" data-dash-sidebar>
    <div class="dash-sidebar__brand">
        <img src="/assets/img/brand/logo-dark.png" alt="<?= View::e($brandLabel) ?>" class="dash-sidebar__brand-logo brand-logo--dark">
        <img src="/assets/img/brand/logo-light.jpg" alt="<?= View::e($brandLabel) ?>" class="dash-sidebar__brand-logo brand-logo--light">
    </div>
    <nav class="dash-sidebar__nav">
        <?php foreach ($navLinks as $key => [$href, $label, $iconName]): ?>
        <a href="<?= View::e($href) ?>" class="dash-sidebar__link<?= $active === $key ? ' is-active' : '' ?>" aria-label="<?= View::e($label) ?>" data-tooltip="<?= View::e($label) ?>">
            <span class="dash-sidebar__icon"><?= View::icon($iconName, 'icon', 20) ?></span>
            <span class="dash-sidebar__label"><?= View::e($label) ?></span>
        </a>
        <?php endforeach; ?>
    </nav>
    <button type="button" class="dash-sidebar__collapse" data-sidebar-collapse aria-label="Collapse sidebar">
        <?= View::icon('chevron-left', 'icon', 18) ?>
    </button>
</aside>
<button type="button" class="dash-sidebar__mobile-overlay" data-sidebar-overlay aria-hidden="true"></button>
