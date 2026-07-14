<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

/** @var string $portal */
$user = Auth::user();
$breadcrumbLabel = trim((string) preg_replace('/\s*—\s*Vipasa Yoga$/', '', $pageTitle ?? ''));
$portalLabel = match ($portal) {
    'admin' => 'Admin',
    'trainer' => 'Trainer',
    default => 'My Account',
};
$profileHref = match ($portal) {
    'admin' => '/admin/settings',
    'trainer' => '/trainer/profile',
    default => '/onboarding/profile',
};
$notificationsHref = match ($portal) {
    'admin' => '/admin/notifications',
    'trainer' => '/trainer/notifications',
    default => '/dashboard/notifications',
};
$initials = mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
?>
<header class="dash-topbar">
    <button type="button" class="dash-topbar__menu-btn" data-sidebar-toggle aria-label="Toggle menu">
        <?= View::icon('menu', 'icon', 20) ?>
    </button>

    <nav class="dash-topbar__breadcrumb" aria-label="Breadcrumb">
        <span class="dash-topbar__breadcrumb-portal"><?= View::e($portalLabel) ?></span>
        <?php if ($breadcrumbLabel !== ''): ?>
        <span class="dash-topbar__breadcrumb-sep">/</span>
        <span class="dash-topbar__breadcrumb-current"><?= View::e($breadcrumbLabel) ?></span>
        <?php endif; ?>
    </nav>

    <div class="dash-topbar__search">
        <?= View::icon('search', 'icon', 16) ?>
        <input type="search" placeholder="Search menu…" data-sidebar-search aria-label="Search navigation">
    </div>

    <div class="dash-topbar__actions">
        <button type="button" class="theme-toggle" data-theme-toggle aria-pressed="false" aria-label="Toggle dark mode">
            <span class="theme-toggle__track">
                <span class="theme-toggle__thumb">
                    <span class="theme-toggle__icon-sun">☀</span>
                    <span class="theme-toggle__icon-moon">☾</span>
                </span>
            </span>
        </button>

        <div class="dash-topbar__dropdown" data-dropdown>
            <button type="button" class="dash-topbar__icon-btn" data-dropdown-toggle aria-label="Notifications">
                <?= View::icon('bell', 'icon', 20) ?>
                <span class="dash-topbar__badge" data-notif-badge hidden>0</span>
            </button>
            <div class="dash-topbar__panel dash-topbar__panel--notifications" data-dropdown-panel hidden>
                <div class="dash-topbar__panel-title">Notifications</div>
                <div data-notif-list>
                    <p class="text-muted" style="padding:var(--space-4);font-size:var(--font-size-sm)">Loading…</p>
                </div>
                <a href="<?= View::e($notificationsHref) ?>" class="dash-topbar__panel-footer">View all</a>
            </div>
        </div>

        <div class="dash-topbar__dropdown" data-dropdown>
            <button type="button" class="dash-topbar__profile-btn" data-dropdown-toggle>
                <span class="dash-topbar__avatar"><?= View::e($initials) ?></span>
                <span class="dash-topbar__profile-name"><?= View::e($user['name'] ?? '') ?></span>
            </button>
            <div class="dash-topbar__panel dash-topbar__panel--profile" data-dropdown-panel hidden>
                <a href="<?= View::e($profileHref) ?>" class="dash-topbar__panel-link">Profile</a>
                <form action="/logout" method="post">
                    <input type="hidden" name="csrf_token" value="<?= View::e(Csrf::token()) ?>">
                    <button type="submit" class="dash-topbar__panel-link dash-topbar__panel-link--button">Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>
