<?php

use App\Core\View;

$pageTitle = 'Calendar — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'calendar';

$statusMeta = [
    'awaiting_trainer_approval' => ['badge-pending', 'Awaiting you'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
];

$monthLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
$firstDayOfWeek = (int) date('N', mktime(0, 0, 0, $month, 1, $year));
$today = date('Y-m-d');

$weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$maxChips = 3;

$cells = array_fill(0, $firstDayOfWeek - 1, null);
for ($day = 1; $day <= $daysInMonth; $day++) {
    $cells[] = $day;
}
while (count($cells) % 7 !== 0) {
    $cells[] = null;
}
$weeks = array_chunk($cells, 7);

$recurringByDay = [];
foreach ($windows as $window) {
    if ((int) $window['is_recurring'] === 1) {
        $recurringByDay[(int) $window['day_of_week']][] = $window;
    }
}
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Calendar</h1>
            <p class="trainer-hero__subtitle">Your bookings, weekly availability, and blocked dates.</p>
        </div>
    </div>

    <div class="flex gap-2 mb-6">
        <a href="/trainer/calendar?view=month" class="btn btn-sm <?= $view === 'month' ? 'btn-primary' : 'btn-secondary' ?>">Month</a>
        <a href="/trainer/calendar?view=week&date=<?= View::e($anchor) ?>" class="btn btn-sm <?= $view === 'week' ? 'btn-primary' : 'btn-secondary' ?>">Week</a>
        <a href="/trainer/calendar?view=day&date=<?= View::e($anchor) ?>" class="btn btn-sm <?= $view === 'day' ? 'btn-primary' : 'btn-secondary' ?>">Day</a>
    </div>

    <?php if ($view === 'month'): ?>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <a href="/trainer/calendar?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-secondary btn-sm">&larr; Prev</a>
        <h2 style="font-size:var(--font-size-lg);margin:0"><?= View::e($monthLabel) ?></h2>
        <a href="/trainer/calendar?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-secondary btn-sm">Next &rarr;</a>
    </div>

    <div class="card mb-8" style="padding:var(--space-4);overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:840px">
            <thead>
                <tr>
                    <?php foreach ($weekdayLabels as $label): ?>
                    <th style="text-align:left;padding:var(--space-2) var(--space-3);font-size:var(--font-size-xs);text-transform:uppercase;letter-spacing:0.03em;color:var(--text-secondary);border-bottom:1px solid var(--border-default)"><?= View::e($label) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weeks as $week): ?>
                <tr>
                    <?php foreach ($week as $day):
                        if ($day === null) {
                            echo '<td style="vertical-align:top;padding:var(--space-2);border:1px solid var(--border-default);background-color:var(--surface-bg-alt)"></td>';
                            continue;
                        }

                        $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $dayBookings = $byDate[$dateKey] ?? [];
                        $count = count($dayBookings);
                        $isToday = $dateKey === $today;
                    ?>
                    <td style="vertical-align:top;padding:var(--space-2);border:1px solid var(--border-default);min-width:110px;<?= $isToday ? 'background-color:var(--color-primary-soft, var(--surface-bg-alt));' : '' ?>">
                        <div class="flex items-center justify-between" style="margin-bottom:var(--space-1)">
                            <span style="font-weight:var(--font-weight-semibold);font-size:var(--font-size-sm)"><?= $day ?></span>
                            <?php if ($count > 0): ?>
                            <span class="badge badge-neutral" style="font-size:9px;padding:2px var(--space-2)"><?= $count ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($count > 0): ?>
                        <div class="flex flex-col gap-1">
                            <?php foreach (array_slice($dayBookings, 0, $maxChips) as $booking):
                                [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
                                $timeLabel = date('g:i A', strtotime($booking['start_time']));
                            ?>
                            <div style="font-size:10px;line-height:1.4" title="<?= View::e($booking['package_name'] . ' — ' . $booking['client_name']) ?>">
                                <span class="text-muted"><?= View::e($timeLabel) ?></span>
                                <span class="badge <?= $badgeClass ?>" style="font-size:8px;padding:1px var(--space-1)"><?= View::e($badgeLabel) ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if ($count > $maxChips): ?>
                            <div class="text-muted" style="font-size:10px">+<?= $count - $maxChips ?> more</div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php elseif ($view === 'week'): ?>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <a href="/trainer/calendar?view=week&date=<?= View::e($weekData['prevAnchor']) ?>" class="btn btn-secondary btn-sm">&larr; Prev week</a>
        <h2 style="font-size:var(--font-size-lg);margin:0"><?= View::e(date('d M', strtotime($weekData['start']))) ?> – <?= View::e(date('d M Y', strtotime($weekData['end']))) ?></h2>
        <a href="/trainer/calendar?view=week&date=<?= View::e($weekData['nextAnchor']) ?>" class="btn btn-secondary btn-sm">Next week &rarr;</a>
    </div>
    <div class="trainer-schedule mb-8">
        <?php foreach ($weekData['days'] as $day): ?>
        <div class="card" style="padding:var(--space-4) var(--space-5);<?= $day['date'] === $today ? 'border-left:3px solid var(--sidebar-active-border, var(--color-accent));' : '' ?>">
            <div class="flex items-center justify-between mb-2">
                <strong><?= View::e(date('D, d M', strtotime($day['date']))) ?></strong>
                <span class="text-muted" style="font-size:var(--font-size-sm)"><?= count($day['bookings']) ?> session(s)</span>
            </div>
            <?php if (empty($day['bookings'])): ?>
            <p class="text-muted" style="margin:0;font-size:var(--font-size-sm)">Nothing scheduled.</p>
            <?php else: ?>
            <div class="flex flex-col gap-2">
                <?php foreach ($day['bookings'] as $booking):
                    [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
                ?>
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <span style="font-size:var(--font-size-sm)"><?= View::e(date('g:i A', strtotime($booking['start_time']))) ?> · <?= View::e($booking['client_name']) ?> · <?= View::e($booking['package_name']) ?></span>
                    <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <a href="/trainer/calendar?view=day&date=<?= View::e($dayData['prevAnchor']) ?>" class="btn btn-secondary btn-sm">&larr; Prev day</a>
        <h2 style="font-size:var(--font-size-lg);margin:0"><?= View::e(date('l, d M Y', strtotime($dayData['date']))) ?></h2>
        <a href="/trainer/calendar?view=day&date=<?= View::e($dayData['nextAnchor']) ?>" class="btn btn-secondary btn-sm">Next day &rarr;</a>
    </div>
    <div class="trainer-schedule mb-8">
        <?php if (empty($dayData['bookings'])): ?>
        <p class="text-muted">Nothing scheduled for this day.</p>
        <?php else: ?>
        <?php foreach ($dayData['bookings'] as $booking):
            [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
        ?>
        <div class="trainer-schedule-row">
            <div class="flex items-center gap-4">
                <span class="trainer-schedule-row__time"><?= View::e(date('g:i A', strtotime($booking['start_time']))) ?> – <?= View::e(date('g:i A', strtotime($booking['end_time']))) ?></span>
                <div>
                    <strong><?= View::e($booking['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package_name']) ?></span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
                <?php if (in_array($booking['status'], ['confirmed', 'completed'], true)): ?>
                <a href="/trainer/sessions/<?= View::e($booking['booking_ref']) ?>" class="btn btn-secondary btn-sm">Session</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Weekly Availability</h2>
    <div class="card mb-8" style="padding:var(--space-6)">
        <form id="availability-form">
            <div class="flex flex-col gap-3">
                <?php foreach ($dayNames as $dow => $name): $existing = $recurringByDay[$dow][0] ?? null; ?>
                <div class="flex items-center gap-3 flex-wrap">
                    <label class="flex items-center gap-2" style="min-width:120px">
                        <input type="checkbox" name="enabled[<?= $dow ?>]" <?= $existing ? 'checked' : '' ?>>
                        <?= View::e($name) ?>
                    </label>
                    <input type="time" name="start[<?= $dow ?>]" class="form-group__control" style="max-width:130px" value="<?= View::e($existing['start_time'] ?? '09:00') ?>">
                    <span class="text-muted">to</span>
                    <input type="time" name="end[<?= $dow ?>]" class="form-group__control" style="max-width:130px" value="<?= View::e($existing['end_time'] ?? '17:00') ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="form-group__error" id="availability-error"></div>
            <button type="submit" class="btn btn-primary mt-4">Save Availability</button>
        </form>
    </div>

    <h2 class="trainer-section-title">Blocked Dates</h2>
    <div class="card" style="padding:var(--space-6)">
        <?php if (empty($blockedDates)): ?>
        <p class="text-muted mb-4">No blocked dates.</p>
        <?php else: ?>
        <div class="flex flex-col gap-2 mb-6">
            <?php foreach ($blockedDates as $blocked): ?>
            <div class="flex items-center justify-between" style="padding:var(--space-2) 0;border-bottom:1px solid var(--border-subtle)">
                <span><?= View::e(date('d M Y', strtotime($blocked['blocked_date']))) ?><?= $blocked['reason'] ? ' — ' . View::e($blocked['reason']) : '' ?><?= $blocked['instructor_id'] === null ? ' <span class="text-muted">(platform holiday)</span>' : '' ?></span>
                <?php if ($blocked['instructor_id'] !== null): ?>
                <button type="button" class="btn btn-ghost btn-sm unblock-btn" data-id="<?= (int) $blocked['id'] ?>">Remove</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form id="block-date-form" class="flex gap-3 flex-wrap items-end">
            <div class="form-group" style="margin:0">
                <input type="date" name="blocked_date" class="form-group__control" required>
                <label class="form-group__label">Date</label>
            </div>
            <div class="form-group" style="margin:0;flex:1;min-width:200px">
                <input type="text" name="reason" class="form-group__control" placeholder=" " maxlength="255">
                <label class="form-group__label">Reason (optional)</label>
            </div>
            <button type="submit" class="btn btn-secondary">Block Date</button>
        </form>
    </div>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.getElementById('availability-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const errorBox = document.getElementById('availability-error');
    errorBox.textContent = '';

    const windows = [];
    for (let dow = 0; dow < 7; dow++) {
        const enabled = form.querySelector(`[name="enabled[${dow}]"]`).checked;
        if (!enabled) continue;
        windows.push({
            day_of_week: dow,
            start_time: form.querySelector(`[name="start[${dow}]"]`).value,
            end_time: form.querySelector(`[name="end[${dow}]"]`).value,
        });
    }

    try {
        await apiPost('/trainer/calendar/availability', { windows });
        toast.success('Availability updated.');
    } catch (err) {
        errorBox.textContent = err.message;
        toast.error(err.message);
    }
});

document.getElementById('block-date-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    try {
        await apiPost('/trainer/calendar/blocked-dates', {
            blocked_date: form.blocked_date.value,
            reason: form.reason.value.trim(),
        });
        window.location.reload();
    } catch (err) {
        toast.error(err.message);
    }
});

document.querySelectorAll('.unblock-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/trainer/calendar/blocked-dates/${btn.dataset.id}/delete`, {});
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});
</script>
