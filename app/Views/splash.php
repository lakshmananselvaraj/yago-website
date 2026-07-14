<?php

use App\Core\View;

$pageTitle = 'Vipasa Yoga';
$pageCss = 'splash';
?>
<div class="splash" id="splash">
    <div class="splash__content">
        <div class="splash__logo" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24 6c-3 4-3 8 0 12 3-4 3-8 0-12Z" fill="var(--color-primary)"/>
                <path d="M24 18c-6 3-10 8-10 14 0 5 4 9 10 9s10-4 10-9c0-6-4-11-10-14Z" fill="var(--color-accent)" opacity="0.85"/>
                <circle cx="24" cy="30" r="4" fill="var(--surface-bg)"/>
            </svg>
        </div>
        <div>
            <div class="splash__wordmark">Vipasa Yoga</div>
            <p class="splash__tagline">Breathe. Move. Belong.</p>
        </div>
        <div class="splash__loader"><div class="splash__loader-fill"></div></div>
    </div>
</div>
<script>
(function () {
    var redirectTo = <?= json_encode($redirectTo) ?>;
    var splash = document.getElementById('splash');
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var delay = prefersReducedMotion ? 400 : 1800;

    setTimeout(function () {
        splash.classList.add('is-leaving');
        setTimeout(function () {
            window.location.href = redirectTo;
        }, prefersReducedMotion ? 0 : 350);
    }, delay);
})();
</script>
