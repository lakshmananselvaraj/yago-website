<?php

use App\Core\View;

$pageTitle = 'Manage Bookings — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'admin';
$active = 'bookings';

$statuses = ['pending_payment', 'awaiting_trainer_approval', 'confirmed', 'completed', 'cancelled', 'rescheduled'];
$statusMeta = [
    'pending_payment' => ['badge-pending', 'Pending payment'],
    'awaiting_trainer_approval' => ['badge-pending', 'Awaiting trainer'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
    'cancelled' => ['badge-cancelled', 'Cancelled'],
    'rescheduled' => ['badge-pending', 'Rescheduled'],
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero">
        <h1 class="services-hero__title">Manage Bookings</h1>
        <p class="services-hero__subtitle">Attach a Google Meet or Zoom link once a session is confirmed.</p>
    </div>

    <div class="services-filters">
        <a href="/admin/bookings" class="chip<?= !$selectedStatus ? ' is-selected' : '' ?>">All</a>
        <?php foreach ($statuses as $status): ?>
        <a href="/admin/bookings?status=<?= urlencode($status) ?>" class="chip<?= $selectedStatus === $status ? ' is-selected' : '' ?>"><?= View::e($statusMeta[$status][1]) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($bookings)): ?>
    <p class="text-muted text-center">No bookings match this filter.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:820px;margin-inline:auto">
        <?php foreach ($bookings as $booking):
            [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
            $dateLabel = date('d M Y', strtotime($booking['slot_date']));
            $timeLabel = date('g:i A', strtotime($booking['start_time']));
            $link = $booking['meeting_link'] ?? null;
        ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-2">
                <div style="font-weight:var(--font-weight-semibold)"><?= View::e($booking['client_name']) ?> <span class="text-muted" style="font-weight:var(--font-weight-regular)">(<?= View::e($booking['client_email']) ?>)</span></div>
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm);margin-bottom:var(--space-4)">
                <?= View::e($booking['package_name']) ?> with <?= View::e($booking['instructor_name']) ?> · <?= View::e($dateLabel) ?> at <?= View::e($timeLabel) ?>
                · <?= View::e($booking['currency']) ?> <?= number_format((float) $booking['total_amount'], 2) ?> · Ref <?= View::e($booking['booking_ref']) ?>
            </p>

            <?php if (in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed'], true)): ?>
            <form class="meeting-link-form flex gap-3 flex-wrap items-center mb-3" data-ref="<?= View::e($booking['booking_ref']) ?>">
                <select name="provider" class="form-group__control" style="max-width:160px">
                    <option value="google_meet" <?= ($link['provider'] ?? '') === 'google_meet' ? 'selected' : '' ?>>Google Meet</option>
                    <option value="zoom" <?= ($link['provider'] ?? '') === 'zoom' ? 'selected' : '' ?>>Zoom</option>
                </select>
                <input type="url" name="url" class="form-group__control" style="flex:1;min-width:220px" placeholder="https://meet.google.com/..." value="<?= View::e($link['url'] ?? '') ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Save link</button>
            </form>
            <?php endif; ?>

            <?php if (in_array($booking['status'], ['awaiting_trainer_approval', 'confirmed'], true)): ?>
            <form class="reschedule-form flex gap-3 flex-wrap items-center" data-ref="<?= View::e($booking['booking_ref']) ?>">
                <input type="date" name="slot_date" class="form-group__control" style="max-width:170px" required>
                <input type="time" name="start_time" class="form-group__control" style="max-width:130px" required>
                <button type="submit" class="btn btn-ghost btn-sm">Reschedule</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-3 mt-8">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="/admin/bookings?page=<?= $p ?><?= $selectedStatus ? '&status=' . urlencode($selectedStatus) : '' ?>" class="chip<?= $p === $page ? ' is-selected' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.querySelectorAll('.meeting-link-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const ref = form.dataset.ref;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;

        try {
            await apiPost(`/admin/bookings/${ref}/meeting-link`, {
                provider: form.querySelector('[name="provider"]').value,
                url: form.querySelector('[name="url"]').value.trim(),
            });
            toast.success('Meeting link saved.');
        } catch (err) {
            toast.error(err.message);
        } finally {
            submitBtn.classList.remove('is-loading');
            submitBtn.disabled = false;
        }
    });
});

document.querySelectorAll('.reschedule-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const ref = form.dataset.ref;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;

        try {
            await apiPost(`/admin/bookings/${ref}/reschedule`, {
                slot_date: form.querySelector('[name="slot_date"]').value,
                start_time: form.querySelector('[name="start_time"]').value,
            });
            toast.success('Booking rescheduled.');
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            submitBtn.classList.remove('is-loading');
            submitBtn.disabled = false;
        }
    });
});
</script>
