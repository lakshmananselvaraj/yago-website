<?php

use App\Core\View;

$pageTitle = 'My Bookings — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'bookings';

$statusMeta = [
    'pending_payment' => ['badge-pending', 'Pending payment'],
    'awaiting_trainer_approval' => ['badge-pending', 'Awaiting confirmation'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
    'cancelled' => ['badge-cancelled', 'Cancelled'],
    'rescheduled' => ['badge-pending', 'Rescheduled'],
];

$renderBooking = static function (array $booking) use ($statusMeta): void {
    [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
    $dateLabel = date('d M Y', strtotime($booking['slot_date']));
    $timeLabel = date('g:i A', strtotime($booking['start_time']));
    $isFuture = strtotime($booking['slot_date'] . ' ' . $booking['start_time']) > time();
    $canCancel = $isFuture && in_array($booking['status'], ['pending_payment', 'awaiting_trainer_approval', 'confirmed'], true);
    $hasInvoice = in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed', 'completed'], true);
    ?>
    <div class="card" style="padding:var(--space-5)">
        <div class="flex items-center justify-between mb-2">
            <div style="font-weight:var(--font-weight-semibold)"><?= View::e($booking['package']['name'] ?? 'Package') ?></div>
            <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
        </div>
        <p class="text-muted" style="font-size:var(--font-size-sm);margin-bottom:var(--space-3)">
            With <?= View::e($booking['instructor']['name'] ?? 'Instructor') ?> · <?= View::e($dateLabel) ?> at <?= View::e($timeLabel) ?>
        </p>
        <p class="text-muted" style="font-size:var(--font-size-sm);margin-bottom:var(--space-4)">
            Ref <?= View::e($booking['booking_ref']) ?> · <?= View::e($booking['currency']) ?> <?= number_format((float) $booking['total_amount'], 2) ?>
        </p>
        <div class="flex gap-3 flex-wrap">
            <?php if ($booking['status'] === 'pending_payment'): ?>
            <a href="/booking/confirm/<?= View::e($booking['booking_ref']) ?>" class="btn btn-accent btn-sm">Complete payment</a>
            <?php endif; ?>
            <?php if ($booking['status'] === 'awaiting_trainer_approval'): ?>
            <span class="text-muted" style="font-size:var(--font-size-sm)">Waiting on your instructor to confirm this session.</span>
            <?php endif; ?>
            <?php if ($hasInvoice): ?>
            <a href="/booking/<?= View::e($booking['booking_ref']) ?>/invoice" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">Invoice</a>
            <?php endif; ?>
            <?php if (!empty($booking['meeting_link']) && $booking['status'] === 'confirmed'): ?>
            <a href="<?= View::e($booking['meeting_link']['url']) ?>" class="btn btn-accent btn-sm" target="_blank" rel="noopener">Join session</a>
            <?php elseif ($booking['status'] === 'confirmed'): ?>
            <span class="text-muted" style="font-size:var(--font-size-sm)">Meeting link will be shared before your session.</span>
            <?php endif; ?>
            <?php if ($canCancel): ?>
            <button type="button" class="btn btn-ghost btn-sm cancel-booking-btn" data-ref="<?= View::e($booking['booking_ref']) ?>">Cancel</button>
            <?php endif; ?>
            <?php if ($booking['status'] === 'confirmed' && $isFuture): ?>
                <?php if ($booking['has_pending_reschedule']): ?>
                <span class="text-muted" style="font-size:var(--font-size-sm)">Reschedule requested — awaiting your instructor.</span>
                <?php else: ?>
                <button type="button" class="btn btn-ghost btn-sm reschedule-btn" data-ref="<?= View::e($booking['booking_ref']) ?>">Request Reschedule</button>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($booking['can_rate']): ?>
            <button type="button" class="btn btn-primary btn-sm rate-btn" data-ref="<?= View::e($booking['booking_ref']) ?>" data-instructor="<?= View::e($booking['instructor']['name'] ?? 'your instructor') ?>">Rate this trainer</button>
            <?php endif; ?>
        </div>
    </div>
    <?php
};
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">My Bookings</h1>
            <p class="services-hero__subtitle" style="margin:0">Your upcoming classes and session history.</p>
        </div>
        <div class="flex gap-3">
            <a href="/dashboard/favorites" class="btn btn-secondary btn-sm">Favorites</a>
            <a href="/dashboard/payments" class="btn btn-secondary btn-sm">Payment History</a>
            <a href="/dashboard/invoices" class="btn btn-secondary btn-sm">Invoices</a>
        </div>
    </div>

    <?php if (empty($upcoming) && empty($history)): ?>
    <p class="text-muted text-center">You haven't booked a session yet. <a href="/services">Browse services</a> to get started.</p>
    <?php else: ?>

    <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Upcoming</h2>
    <?php if (empty($upcoming)): ?>
    <p class="text-muted" style="margin-bottom:var(--space-8)">No upcoming sessions. <a href="/services">Book one</a>.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4 mb-8" style="max-width:720px;margin-inline:auto">
        <?php foreach ($upcoming as $booking) {
            $renderBooking($booking);
        } ?>
    </div>
    <?php endif; ?>

    <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Completed &amp; Past</h2>
    <?php if (empty($history)): ?>
    <p class="text-muted">Nothing here yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:720px;margin-inline:auto">
        <?php foreach ($history as $booking) {
            $renderBooking($booking);
        } ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<div class="modal-overlay hidden" id="rate-modal-overlay">
    <div class="modal">
        <div class="modal__header">
            <div class="modal__title">Rate <span id="rate-modal-instructor"></span></div>
            <button type="button" class="modal__close" id="rate-modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="rate-form">
            <div class="modal__body flex flex-col gap-4">
                <div class="form-group" style="margin:0">
                    <select name="rating" class="form-group__control" required>
                        <option value="">Choose a rating</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?> / 5</option>
                        <?php endfor; ?>
                    </select>
                    <label class="form-group__label">Rating</label>
                </div>
                <div class="form-group" style="margin:0">
                    <textarea name="review_text" class="form-group__control" placeholder=" " rows="3"></textarea>
                    <label class="form-group__label">Review (optional)</label>
                </div>
                <div class="form-group__error" id="rate-form-error"></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" id="rate-modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Rating</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay hidden" id="reschedule-modal-overlay">
    <div class="modal">
        <div class="modal__header">
            <div class="modal__title">Request Reschedule</div>
            <button type="button" class="modal__close" id="reschedule-modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="reschedule-form">
            <div class="modal__body flex flex-col gap-4">
                <p class="text-muted" style="margin:0;font-size:var(--font-size-sm)">Your instructor will review and approve or decline this request.</p>
                <div class="form-group" style="margin:0">
                    <input type="date" name="slot_date" class="form-group__control" required>
                    <label class="form-group__label">New date</label>
                </div>
                <div class="form-group" style="margin:0">
                    <input type="time" name="start_time" class="form-group__control" required>
                    <label class="form-group__label">New time</label>
                </div>
                <div class="form-group__error" id="reschedule-form-error"></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" id="reschedule-modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Send Request</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.querySelectorAll('.cancel-booking-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (!confirm('Cancel this booking? This cannot be undone.')) return;

        btn.classList.add('is-loading');
        btn.disabled = true;

        try {
            await apiPost(`/api/bookings/${btn.dataset.ref}/cancel`, {});
            toast.success('Booking cancelled.');
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});

const rateOverlay = document.getElementById('rate-modal-overlay');
const rateForm = document.getElementById('rate-form');
const rateErrorBox = document.getElementById('rate-form-error');
let ratingRef = null;

function closeRateModal() {
    rateOverlay.classList.add('hidden');
    rateForm.reset();
    rateErrorBox.textContent = '';
    ratingRef = null;
}

document.querySelectorAll('.rate-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        ratingRef = btn.dataset.ref;
        document.getElementById('rate-modal-instructor').textContent = btn.dataset.instructor;
        rateOverlay.classList.remove('hidden');
    });
});

document.getElementById('rate-modal-close').addEventListener('click', closeRateModal);
document.getElementById('rate-modal-cancel').addEventListener('click', closeRateModal);
rateOverlay.addEventListener('click', (event) => { if (event.target === rateOverlay) closeRateModal(); });

rateForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    rateErrorBox.textContent = '';

    try {
        await apiPost(`/api/bookings/${ratingRef}/review`, {
            rating: Number(rateForm.rating.value),
            review_text: rateForm.review_text.value.trim(),
        });
        toast.success('Thanks for your feedback!');
        window.location.reload();
    } catch (err) {
        rateErrorBox.textContent = err.message;
        toast.error(err.message);
    }
});

const rescheduleOverlay = document.getElementById('reschedule-modal-overlay');
const rescheduleForm = document.getElementById('reschedule-form');
const rescheduleErrorBox = document.getElementById('reschedule-form-error');
let rescheduleRef = null;

function closeRescheduleModal() {
    rescheduleOverlay.classList.add('hidden');
    rescheduleForm.reset();
    rescheduleErrorBox.textContent = '';
    rescheduleRef = null;
}

document.querySelectorAll('.reschedule-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        rescheduleRef = btn.dataset.ref;
        rescheduleOverlay.classList.remove('hidden');
    });
});

document.getElementById('reschedule-modal-close').addEventListener('click', closeRescheduleModal);
document.getElementById('reschedule-modal-cancel').addEventListener('click', closeRescheduleModal);
rescheduleOverlay.addEventListener('click', (event) => { if (event.target === rescheduleOverlay) closeRescheduleModal(); });

rescheduleForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    rescheduleErrorBox.textContent = '';

    try {
        await apiPost(`/api/bookings/${rescheduleRef}/request-reschedule`, {
            slot_date: rescheduleForm.slot_date.value,
            start_time: rescheduleForm.start_time.value,
        });
        toast.success('Reschedule request sent.');
        window.location.reload();
    } catch (err) {
        rescheduleErrorBox.textContent = err.message;
        toast.error(err.message);
    }
});
</script>
