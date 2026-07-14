<?php

use App\Core\View;

$pageTitle = 'Reviews — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'reviews';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Reviews</h1>
            <p class="trainer-hero__subtitle">What students have said about sessions with you.</p>
        </div>
    </div>

    <div class="trainer-stat-row">
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= $ratingCount > 0 ? number_format($ratingAvg, 1) : 'New' ?></div>
            <div class="trainer-stat__label">Average rating</div>
        </div>
        <div class="trainer-stat">
            <div class="trainer-stat__value"><?= (int) $ratingCount ?></div>
            <div class="trainer-stat__label">Total reviews</div>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
    <p class="text-muted text-center">No reviews yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-3">
        <?php foreach ($reviews as $review): ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-2">
                <div class="rating-stars">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <?= View::icon('star', 'icon' . ($i < (int) $review['rating'] ? '' : ' is-empty'), 16) ?>
                    <?php endfor; ?>
                </div>
                <span class="text-muted" style="font-size:var(--font-size-sm)"><?= View::e(date('d M Y', strtotime($review['created_at']))) ?></span>
            </div>
            <?php if (!empty($review['review_text'])): ?>
            <p style="margin:0"><?= View::e($review['review_text']) ?></p>
            <?php else: ?>
            <p class="text-muted" style="margin:0">No written feedback.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
