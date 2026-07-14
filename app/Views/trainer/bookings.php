<?php

use App\Core\View;

$pageTitle = 'My Bookings — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'bookings';

$statusMeta = [
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
    'cancelled' => ['badge-cancelled', 'Cancelled'],
    'rescheduled' => ['badge-pending', 'Rescheduled'],
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Bookings</h1>
            <p class="trainer-hero__subtitle">Review requests and manage your confirmed sessions.</p>
        </div>
    </div>

    <?php if (!empty($rescheduleRequests)): ?>
    <h2 class="trainer-section-title">Reschedule Requests</h2>
    <div class="trainer-schedule mb-8">
        <?php foreach ($rescheduleRequests as $reschedule): ?>
        <div class="trainer-schedule-row" data-reschedule-row data-id="<?= (int) $reschedule['id'] ?>">
            <div class="flex items-center gap-4">
                <div>
                    <strong><?= View::e($reschedule['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($reschedule['package_name']) ?> · Ref <?= View::e($reschedule['booking_ref']) ?></span>
                    <div class="text-muted" style="font-size:var(--font-size-sm)">
                        Currently <?= View::e(date('d M Y', strtotime($reschedule['slot_date']))) ?> at <?= View::e(date('g:i A', strtotime($reschedule['start_time']))) ?>
                        &rarr; requested <strong><?= View::e(date('d M Y', strtotime($reschedule['requested_slot_date']))) ?> at <?= View::e(date('g:i A', strtotime($reschedule['requested_start_time']))) ?></strong>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" class="btn btn-primary btn-sm approve-reschedule-btn" data-id="<?= (int) $reschedule['id'] ?>">Approve</button>
                <button type="button" class="btn btn-ghost btn-sm decline-reschedule-btn" data-id="<?= (int) $reschedule['id'] ?>">Decline</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Awaiting Your Approval</h2>
    <?php if (empty($awaiting)): ?>
    <p class="text-muted mb-8">Nothing waiting on you right now.</p>
    <?php else: ?>
    <div class="trainer-schedule">
        <?php foreach ($awaiting as $booking): ?>
        <div class="trainer-schedule-row" data-booking-row data-ref="<?= View::e($booking['booking_ref']) ?>">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('d M Y', strtotime($booking['slot_date']))) ?> · <?= View::e(date('g:i A', strtotime($booking['start_time']))) ?></span>
                <div>
                    <strong><?= View::e($booking['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package']['name'] ?? 'Session') ?> · Ref <?= View::e($booking['booking_ref']) ?></span>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" class="btn btn-primary btn-sm accept-btn" data-ref="<?= View::e($booking['booking_ref']) ?>">Accept</button>
                <button type="button" class="btn btn-ghost btn-sm reject-btn" data-ref="<?= View::e($booking['booking_ref']) ?>">Reject</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Confirmed &amp; Completed</h2>
    <?php if (empty($confirmed)): ?>
    <p class="text-muted mb-8">No confirmed sessions yet.</p>
    <?php else: ?>
    <div class="trainer-schedule">
        <?php foreach ($confirmed as $booking):
            [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
        ?>
        <div class="trainer-schedule-row">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('d M Y', strtotime($booking['slot_date']))) ?> · <?= View::e(date('g:i A', strtotime($booking['start_time']))) ?></span>
                <div>
                    <strong><?= View::e($booking['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package']['name'] ?? 'Session') ?></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
                <a href="/trainer/sessions/<?= View::e($booking['booking_ref']) ?>" class="btn btn-secondary btn-sm">Manage</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($other)): ?>
    <h2 class="trainer-section-title">Cancelled</h2>
    <div class="trainer-schedule">
        <?php foreach ($other as $booking):
            [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
        ?>
        <div class="trainer-schedule-row">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('d M Y', strtotime($booking['slot_date']))) ?></span>
                <div>
                    <strong><?= View::e($booking['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package']['name'] ?? 'Session') ?></span>
                </div>
            </div>
            <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.querySelectorAll('.accept-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/trainer/bookings/${btn.dataset.ref}/accept`, {});
            toast.success('Booking confirmed.');
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});

document.querySelectorAll('.reject-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (!confirm('Reject this booking? The client will be notified and refunded.')) return;
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/trainer/bookings/${btn.dataset.ref}/reject`, {});
            toast.success('Booking rejected.');
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});

document.querySelectorAll('.approve-reschedule-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/trainer/reschedule-requests/${btn.dataset.id}/approve`, {});
            toast.success('Reschedule approved.');
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});

document.querySelectorAll('.decline-reschedule-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (!confirm('Decline this reschedule request? The original time stays booked.')) return;
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/trainer/reschedule-requests/${btn.dataset.id}/decline`, {});
            toast.success('Reschedule request declined.');
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});
</script>
