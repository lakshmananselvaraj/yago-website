<?php

use App\Core\View;

$pageTitle = 'My Favorites — Vipasa Yoga';
$pageCss = 'instructors';
$portal = 'client';
$active = 'favorites';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">My Favorites</h1>
            <p class="services-hero__subtitle" style="margin:0">Instructors you've saved for later.</p>
        </div>
        <a href="/dashboard/bookings" class="btn btn-secondary btn-sm">My Bookings</a>
    </div>

    <?php if (empty($favorites)): ?>
    <p class="text-muted text-center">No favorites yet. <a href="/instructors">Browse instructors</a> and tap "Add to Favorites" on their profile.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:720px;margin-inline:auto">
        <?php foreach ($favorites as $favorite): ?>
        <div class="card flex items-center justify-between flex-wrap gap-3" style="padding:var(--space-5)" data-instructor-id="<?= (int) $favorite['instructor_id'] ?>">
            <div class="flex items-center gap-4">
                <?php if (!empty($favorite['avatar_path'])): ?>
                <img src="<?= View::e($favorite['avatar_path']) ?>" alt="" style="width:56px;height:56px;border-radius:50%;object-fit:cover">
                <?php endif; ?>
                <div>
                    <strong><?= View::e($favorite['name']) ?></strong>
                    <?php if (!empty($favorite['headline'])): ?>
                    <p class="text-muted" style="margin:0;font-size:var(--font-size-sm)"><?= View::e($favorite['headline']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="/instructors/<?= (int) $favorite['instructor_id'] ?>" class="btn btn-secondary btn-sm">View Profile</a>
                <button type="button" class="btn btn-ghost btn-sm unfavorite-btn" data-id="<?= (int) $favorite['instructor_id'] ?>">Remove</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.querySelectorAll('.unfavorite-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/api/instructors/${btn.dataset.id}/favorite`, {});
            btn.closest('[data-instructor-id]').remove();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});
</script>
