<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

$pageTitle = $pageTitle ?? 'Vipasa Yoga';
$pageCss = $pageCss ?? null;
$user = Auth::user();
$brandHref = match ($user['role'] ?? null) {
    'admin' => '/admin',
    'instructor' => '/trainer/dashboard',
    'client' => '/dashboard',
    default => '/',
};
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= View::e($pageTitle) ?></title>
<meta name="csrf-token" content="<?= View::e(Csrf::token()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Inter:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/assets/css/design-tokens.css">
<link rel="stylesheet" href="/assets/css/base.css">
<link rel="stylesheet" href="/assets/css/components.css">
<?php if ($pageCss): ?>
<link rel="stylesheet" href="/assets/css/pages/<?= View::e($pageCss) ?>.css">
<?php endif; ?>
<script>
(function () {
    var saved = localStorage.getItem('vipasa-theme');
    if (saved === 'dark' || saved === 'light') {
        document.documentElement.setAttribute('data-theme', saved);
    }
})();
</script>
</head>
<body>
<header class="nav-glass no-print">
    <div class="nav-glass__brand">
        <a href="<?= View::e($brandHref) ?>">
            <img src="/assets/img/brand/logo-dark.png" alt="Vipasa Yoga" class="nav-glass__brand-logo brand-logo--dark">
            <img src="/assets/img/brand/logo-light.jpg" alt="Vipasa Yoga" class="nav-glass__brand-logo brand-logo--light">
        </a>
    </div>
    <button type="button" class="nav-glass__toggle" data-nav-toggle aria-expanded="false" aria-label="Toggle menu">
        <span class="nav-glass__toggle-bar"></span>
        <span class="nav-glass__toggle-bar"></span>
        <span class="nav-glass__toggle-bar"></span>
    </button>
    <nav class="nav-glass__links">
        <?php if ($user && $user['role'] === 'instructor'): ?>
        <a href="/trainer/dashboard" class="nav-glass__link">Dashboard</a>
        <a href="/trainer/bookings" class="nav-glass__link">Bookings</a>
        <a href="/trainer/calendar" class="nav-glass__link">Calendar</a>
        <a href="/trainer/profile" class="nav-glass__link">Profile</a>
        <?php elseif ($user && $user['role'] === 'admin'): ?>
        <a href="/admin" class="nav-glass__link">Admin</a>
        <?php else: ?>
        <a href="/services" class="nav-glass__link">Services</a>
        <a href="/instructors" class="nav-glass__link">Instructors</a>
        <?php if ($user): ?>
        <a href="/dashboard" class="nav-glass__link">Dashboard</a>
        <a href="/dashboard/bookings" class="nav-glass__link">My Bookings</a>
        <a href="/onboarding/profile" class="nav-glass__link">Profile</a>
        <?php endif; ?>
        <?php endif; ?>
    </nav>
    <div class="nav-glass__actions">
        <button type="button" class="theme-toggle" data-theme-toggle aria-pressed="false" aria-label="Toggle dark mode">
            <span class="theme-toggle__track">
                <span class="theme-toggle__thumb">
                    <span class="theme-toggle__icon-sun">☀</span>
                    <span class="theme-toggle__icon-moon">☾</span>
                </span>
            </span>
        </button>
        <?php if ($user): ?>
        <span class="chip"><?= View::e($user['name']) ?></span>
        <form action="/logout" method="post" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= View::e(Csrf::token()) ?>">
            <button type="submit" class="btn btn-ghost btn-sm">Log out</button>
        </form>
        <?php else: ?>
        <a href="/login" class="btn btn-ghost btn-sm">Log in</a>
        <a href="/signup" class="btn btn-accent btn-sm">Get started</a>
        <?php endif; ?>
    </div>
</header>
<main>
<?= $content ?>
</main>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
