<?php

use App\Core\View;

$pageTitle = 'Services — Vipasa Yoga';
$pageCss = 'services';
$portal = 'client';
$active = 'services';
?>
<div class="services-page container page-enter">
    <div class="services-hero">
        <h1 class="services-hero__title">Browse Yoga Services</h1>
        <p class="services-hero__subtitle">Choose a package, then pick the instructor who's right for you.</p>
    </div>

    <?php if (empty($packages)): ?>
    <p class="text-muted text-center">No packages are available right now. Please check back soon.</p>
    <?php else: ?>
    <div class="service-grid">
        <?php
        $packagePhotos = [
            'Single Session' => '/assets/img/client/pose-side-stretch.webp',
            'Weekly Package' => '/assets/img/client/pose-crow-2.webp',
            'Monthly Package' => '/assets/img/client/hero-banner.webp',
        ];
        ?>
        <?php foreach ($packages as $i => $package): ?>
        <div class="package-card<?= $i === 1 ? ' is-featured' : '' ?>" data-reveal data-reveal-delay="<?= ($i % 3) + 1 ?>">
            <?php if ($i === 1): ?>
            <span class="package-card__ribbon">Most popular</span>
            <?php endif; ?>
            <?php if (isset($packagePhotos[$package['name']])): ?>
            <div class="package-card__media">
                <img src="<?= View::e($packagePhotos[$package['name']]) ?>" alt="" loading="lazy">
            </div>
            <?php endif; ?>
            <div class="package-card__name"><?= (int) $package['sessions_count'] ?> session<?= (int) $package['sessions_count'] > 1 ? 's' : '' ?></div>
            <h3><?= View::e($package['name']) ?></h3>
            <div class="package-card__price">
                <span class="package-card__price-value"><?= View::e($package['currency']) ?> <?= number_format((float) $package['price'], 2) ?></span>
            </div>
            <p class="text-muted"><?= View::e($package['description']) ?></p>
            <div class="package-card__features">
                <div class="package-card__feature"><?= (int) $package['duration_minutes'] ?> minutes per session</div>
                <div class="package-card__feature">Up to <?= (int) $package['max_participants'] ?> participant<?= (int) $package['max_participants'] > 1 ? 's' : '' ?></div>
            </div>
            <a href="/instructors?package=<?= (int) $package['id'] ?>" class="btn btn-accent btn-block">Choose instructor</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
