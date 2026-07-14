<?php

use App\Core\View;

$pageTitle = 'Gallery — Vipasa Yoga';
$pageCss = 'gallery';
$hideFloatingThemeToggle = true;

$categories = [
    'all' => 'All',
    'poses' => 'Poses',
    'inversions' => 'Inversions',
    'stillness' => 'Stillness',
];
?>
<header class="nav-glass">
    <div class="nav-glass__brand">
        <a href="/#hero">
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
        <a href="/#about" class="nav-glass__link">About</a>
        <a href="/#packages" class="nav-glass__link">Packages</a>
        <a href="/#instructors" class="nav-glass__link">Instructors</a>
        <a href="/gallery" class="nav-glass__link">Gallery</a>
        <a href="/#faq" class="nav-glass__link">FAQ</a>
        <a href="/#contact" class="nav-glass__link">Contact</a>
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
        <a href="/login" class="btn btn-ghost btn-sm">Log in</a>
        <a href="/signup" class="btn btn-accent btn-sm">Get started</a>
    </div>
</header>

<section class="gallery-hero">
    <div class="container">
        <div class="section-heading" data-reveal>
            <span class="section-heading__eyebrow">Gallery</span>
            <h1 class="section-heading__title">Inside the practice</h1>
            <p class="section-heading__subtitle">A look at real sessions, real instructors, and the moments in between.</p>
        </div>
        <div class="gallery-controls">
            <div class="gallery-filters" role="tablist" aria-label="Filter gallery by category">
                <?php foreach ($categories as $key => $label): ?>
                <button type="button" class="chip gallery-filters__chip<?= $key === 'all' ? ' is-selected' : '' ?>" data-filter="<?= View::e($key) ?>"><?= View::e($label) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="gallery-view-toggle" role="group" aria-label="Switch layout">
                <button type="button" class="gallery-view-toggle__btn is-active" data-view="grid" aria-label="Grid view">
                    <?= View::icon('grid') ?>
                </button>
                <button type="button" class="gallery-view-toggle__btn" data-view="masonry" aria-label="Masonry view">
                    <?= View::icon('masonry') ?>
                </button>
            </div>
        </div>
    </div>
</section>

<section class="landing-section gallery-section">
    <div class="container">
        <div class="gallery-grid" id="gallery-grid" data-view="grid">
            <?php foreach ($photos as $i => $photo): ?>
            <figure class="gallery-item" data-category="<?= View::e($photo['category']) ?>" data-reveal data-reveal-delay="<?= ($i % 3) + 1 ?>">
                <button type="button" class="gallery-item__trigger" data-lightbox-index="<?= $i ?>" aria-label="View larger: <?= View::e($photo['caption']) ?>">
                    <img src="<?= View::e($photo['path']) ?>" alt="<?= View::e($photo['caption']) ?>" loading="lazy">
                    <span class="gallery-item__zoom" aria-hidden="true">
                        <?= View::icon('zoom-in') ?>
                    </span>
                </button>
                <figcaption class="gallery-item__caption"><?= View::e($photo['caption']) ?></figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
        <p class="text-muted text-center mt-8 gallery-empty" hidden>No photos in this category yet.</p>
    </div>
</section>

<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox__close" data-lightbox-close aria-label="Close">
        <?= View::icon('x') ?>
    </button>
    <button type="button" class="lightbox__nav lightbox__nav--prev" data-lightbox-prev aria-label="Previous photo">
        <?= View::icon('chevron-left') ?>
    </button>
    <button type="button" class="lightbox__nav lightbox__nav--next" data-lightbox-next aria-label="Next photo">
        <?= View::icon('chevron-right') ?>
    </button>
    <figure class="lightbox__figure">
        <img src="" alt="" id="lightbox-img">
        <figcaption id="lightbox-caption"></figcaption>
    </figure>
</div>

<footer class="site-footer">
    <div class="container site-footer__bottom-inner" style="padding-block: var(--space-8)">
        <p class="site-footer__copyright">&copy; <?= date('Y') ?> Vipasa Yoga. <a href="/">Back to home</a></p>
    </div>
</footer>
<script type="module">
import { initGallery } from '/assets/js/modules/gallery.js';
initGallery();
</script>
