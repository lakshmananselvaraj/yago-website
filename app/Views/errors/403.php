<?php

use App\Core\Auth;

$pageTitle = 'Access denied';
$pageCss = null;

$homeByRole = [
    'admin' => '/admin',
    'instructor' => '/trainer/dashboard',
    'client' => '/services',
];
$user = Auth::user();
$homeHref = $user ? ($homeByRole[$user['role']] ?? '/services') : '/login';
?>
<div class="container" style="min-height:60vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:var(--space-4);">
    <h1>403</h1>
    <p class="text-muted">You don't have permission to access this page.</p>
    <a href="<?= htmlspecialchars($homeHref, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Back to your dashboard</a>
</div>
