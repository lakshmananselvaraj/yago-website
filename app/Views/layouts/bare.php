<?php

use App\Core\Csrf;
use App\Core\View;

$pageTitle = $pageTitle ?? 'Vipasa Yoga';
$pageCss = $pageCss ?? null;
$hideFloatingThemeToggle = $hideFloatingThemeToggle ?? false;
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
<?php if (!$hideFloatingThemeToggle): ?>
<button type="button" class="theme-toggle no-print" data-theme-toggle aria-pressed="false" aria-label="Toggle dark mode" style="position:fixed;top:var(--space-4,1rem);right:var(--space-4,1rem);z-index:50">
    <span class="theme-toggle__track">
        <span class="theme-toggle__thumb">
            <span class="theme-toggle__icon-sun">☀</span>
            <span class="theme-toggle__icon-moon">☾</span>
        </span>
    </span>
</button>
<?php endif; ?>
<?= $content ?>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
