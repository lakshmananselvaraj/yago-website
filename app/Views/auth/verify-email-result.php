<?php

use App\Core\View;

$pageTitle = 'Email verification — Vipasa Yoga';
$pageCss = 'auth';
?>
<div class="auth-page">
    <div class="auth-card text-center">
        <?php if ($success): ?>
        <div class="booking-success__icon" style="margin-inline:auto">
            <?= View::icon('check', 'icon', 24) ?>
        </div>
        <h1 class="auth-card__title">Email verified</h1>
        <p class="auth-card__subtitle">Thanks for confirming your email address.</p>
        <?php else: ?>
        <h1 class="auth-card__title">Link expired</h1>
        <p class="auth-card__subtitle">This verification link is invalid or has expired.</p>
        <?php endif; ?>
        <a href="/services" class="btn btn-primary btn-block">Continue to Vipasa Yoga</a>
    </div>
</div>
