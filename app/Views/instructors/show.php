<?php

use App\Core\View;

$pageTitle = ($instructor['headline'] ?: 'Instructor') . ' — Vipasa Yoga';
$pageCss = 'instructors';
$portal = 'client';
$active = 'instructors';

$weekdayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$recurringByDay = array_fill(0, 7, []);
foreach ($availability as $window) {
    if ((int) $window['is_recurring'] === 1) {
        $recurringByDay[(int) $window['day_of_week']][] = $window;
    }
}
$certificates = $instructor['certificates'] ?? [];
$specialties = $instructor['specialties'] ?? [];
$rating = (float) ($instructor['rating_avg'] ?? 0);
$fullStars = (int) round($rating);
$bookHref = '/booking/schedule?instructor_id=' . (int) $instructor['id'] . ($packageId ? '&package_id=' . urlencode((string) $packageId) : '');
$displayName = $instructor['name'] ?? ($instructor['headline'] ?: 'Yoga Instructor');
$initials = mb_strtoupper(implode('', array_map(static fn ($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($displayName)), 0, 2))));
$accentPalette = ['var(--color-primary)', 'var(--color-accent)', 'var(--color-tertiary)'];
$accentSoftPalette = ['var(--color-primary-soft)', 'var(--color-accent-soft)', 'var(--color-tertiary-soft)'];
$accentIndex = (int) $instructor['id'] % 3;
?>
<div class="instructors-page container page-enter">
    <div class="instructor-profile__header">
        <div class="instructor-profile__avatar">
            <?php if (!empty($instructor['avatar_path'])): ?>
            <img src="<?= View::e($instructor['avatar_path']) ?>" alt="">
            <?php else: ?>
            <div class="flex items-center justify-center" style="width:100%;height:100%;background:<?= $accentSoftPalette[$accentIndex] ?>;color:<?= $accentPalette[$accentIndex] ?>;font-weight:var(--font-weight-semibold);font-size:var(--font-size-2xl)">
                <?= View::e($initials ?: 'YG') ?>
            </div>
            <?php endif; ?>
        </div>
        <div>
            <div class="instructor-profile__name"><?= View::e($displayName) ?></div>
            <?php if (!empty($instructor['headline'])): ?>
            <p class="text-muted" style="margin:0 0 var(--space-2)"><?= View::e($instructor['headline']) ?></p>
            <?php endif; ?>
            <div class="instructor-profile__meta">
                <?php if ((int) ($instructor['rating_count'] ?? 0) > 0): ?>
                <span class="rating-stars">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <?= View::icon('star', 'star' . ($i < $fullStars ? '' : ' is-empty')) ?>
                    <?php endfor; ?>
                    <span class="rating-stars__value"><?= number_format($rating, 1) ?></span>
                    <span class="rating-stars__count">(<?= (int) $instructor['rating_count'] ?> reviews)</span>
                </span>
                <?php else: ?>
                <span class="badge">New</span>
                <?php endif; ?>
                <?php if ((int) $instructor['experience_years'] > 0): ?>
                <span class="flex items-center gap-2"><?= View::icon('clock', 'icon', 16) ?> <?= (int) $instructor['experience_years'] ?> years experience</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($certificates)): ?>
            <div class="instructor-profile__certificates">
                <?php foreach ($certificates as $cert): ?>
                <span class="chip flex items-center gap-1"><?= View::icon('certificate', 'icon', 14) ?> <?= View::e((string) $cert) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="instructor-profile__actions">
            <a href="<?= View::e($bookHref) ?>" class="btn btn-accent btn-lg" id="book-btn" <?= $packageId ? '' : 'data-needs-package' ?>>Book this instructor</a>
            <button type="button" class="btn btn-secondary btn-lg" id="favorite-btn" data-favorited="<?= $isFavorited ? '1' : '0' ?>">
                <?= $isFavorited ? '♥ Favorited' : '♡ Add to Favorites' ?>
            </button>
        </div>
    </div>

    <div class="instructor-profile__layout">
        <div class="instructor-profile__main">
            <div class="instructor-profile__section">
                <div class="instructor-profile__section-title">About</div>
                <p class="instructor-profile__bio"><?= nl2br(View::e($instructor['bio'] ?: 'No bio provided yet.')) ?></p>
                <?php if (!empty($specialties)): ?>
                <div class="instructor-card__tags mt-4">
                    <?php foreach ($specialties as $tag): ?>
                    <span class="chip"><?= View::e((string) $tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="instructor-profile__section">
                <div class="instructor-profile__section-title">Reviews (<?= count($reviews) ?>)</div>
                <?php if (empty($reviews)): ?>
                <p class="text-muted">No reviews yet.</p>
                <?php else: ?>
                <div class="review-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="avatar avatar-sm">
                            <?= View::icon('user', 'icon', 16) ?>
                        </div>
                        <div class="review-item__body">
                            <div class="review-item__header">
                                <span class="review-item__name">Verified student</span>
                                <span class="review-item__date"><?= View::e(date('M j, Y', strtotime($review['created_at']))) ?></span>
                            </div>
                            <div class="rating-stars">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                <?= View::icon('star', 'star' . ($i < (int) $review['rating'] ? '' : ' is-empty')) ?>
                                <?php endfor; ?>
                            </div>
                            <?php if (!empty($review['review_text'])): ?>
                            <p class="review-item__text"><?= View::e($review['review_text']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="instructor-profile__sidebar">
            <div class="instructor-profile__section">
                <div class="instructor-profile__section-title">Weekly availability</div>
                <?php $any = false; foreach ($recurringByDay as $dayIndex => $windows): if (empty($windows)) continue; $any = true; ?>
                <div class="flex justify-between mb-2">
                    <span><?= View::e($weekdayNames[$dayIndex]) ?></span>
                    <span class="text-muted">
                        <?php
                        $parts = [];
                        foreach ($windows as $w) {
                            $parts[] = substr($w['start_time'], 0, 5) . '–' . substr($w['end_time'], 0, 5);
                        }
                        echo View::e(implode(', ', $parts));
                        ?>
                    </span>
                </div>
                <?php endforeach; ?>
                <?php if (!$any): ?>
                <p class="text-muted">No recurring availability set.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

const favoriteBtn = document.getElementById('favorite-btn');
favoriteBtn.addEventListener('click', async () => {
    favoriteBtn.classList.add('is-loading');
    favoriteBtn.disabled = true;
    try {
        const result = await apiPost('/api/instructors/<?= (int) $instructor['id'] ?>/favorite', {});
        const isFavorited = result.data.is_favorited;
        favoriteBtn.dataset.favorited = isFavorited ? '1' : '0';
        favoriteBtn.textContent = isFavorited ? '♥ Favorited' : '♡ Add to Favorites';
        toast.success(result.message);
    } catch (err) {
        toast.error(err.message);
    } finally {
        favoriteBtn.classList.remove('is-loading');
        favoriteBtn.disabled = false;
    }
});
</script>

<?php if (!$packageId): ?>
<div class="modal-overlay hidden" id="package-modal-overlay">
    <div class="modal modal-lg">
        <div class="modal__header">
            <div>
                <div class="modal__title">Choose a package</div>
                <p class="text-muted" style="font-size:var(--font-size-sm)">Pick a package to book this instructor.</p>
            </div>
            <button type="button" class="modal__close" id="package-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="modal__body" id="package-modal-body"></div>
    </div>
</div>
<script type="module">
import { apiGet } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';
import { showSkeleton, clearSkeleton } from '/assets/js/modules/skeleton.js';

const instructorId = <?= (int) $instructor['id'] ?>;
const bookBtn = document.getElementById('book-btn');
const overlay = document.getElementById('package-modal-overlay');
const modalBody = document.getElementById('package-modal-body');
const closeBtn = document.getElementById('package-modal-close');

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

function closeModal() {
    overlay.classList.add('hidden');
    modalBody.innerHTML = '';
}
closeBtn.addEventListener('click', closeModal);
overlay.addEventListener('click', (event) => { if (event.target === overlay) closeModal(); });

bookBtn.addEventListener('click', async (event) => {
    event.preventDefault();
    overlay.classList.remove('hidden');
    showSkeleton(modalBody, 'card', 3);

    try {
        const servicesResult = await apiGet('/api/services');
        const serviceTypes = servicesResult.data.service_types;
        const allPackages = [];

        for (const service of serviceTypes) {
            const pkgResult = await apiGet(`/api/services/${service.id}/packages`);
            pkgResult.data.packages.forEach((pkg) => allPackages.push({ ...pkg, serviceName: service.name }));
        }

        clearSkeleton(modalBody);

        if (!allPackages.length) {
            modalBody.innerHTML = '<p class="text-muted">No packages are available right now.</p>';
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-2 gap-4';
        allPackages.forEach((pkg) => {
            const pkgCard = document.createElement('a');
            pkgCard.className = 'package-card';
            pkgCard.href = `/booking/schedule?instructor_id=${instructorId}&package_id=${pkg.id}`;
            pkgCard.innerHTML = `
                <div class="package-card__name">${escapeHtml(pkg.serviceName)}</div>
                <h3>${escapeHtml(pkg.name)}</h3>
                <div class="package-card__price">
                    <span class="package-card__price-value">${escapeHtml(pkg.currency)} ${Number(pkg.price).toFixed(2)}</span>
                </div>
                <div class="package-card__features">
                    <div class="package-card__feature">${pkg.duration_minutes} minutes · ${pkg.sessions_count} session${pkg.sessions_count > 1 ? 's' : ''}</div>
                </div>
                <span class="btn btn-accent btn-block">Select</span>
            `;
            grid.appendChild(pkgCard);
        });
        modalBody.innerHTML = '';
        modalBody.appendChild(grid);
    } catch (err) {
        clearSkeleton(modalBody);
        modalBody.innerHTML = '<p class="text-muted">Something went wrong loading packages.</p>';
        toast.error(err.message);
    }
});
</script>
<?php endif; ?>
