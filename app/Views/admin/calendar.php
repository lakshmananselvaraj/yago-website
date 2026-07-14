<?php

use App\Core\View;

$pageTitle = 'Calendar — Vipasa Yoga';
$pageCss = 'services';
$portal = 'admin';
$active = 'calendar';

$statusMeta = [
    'pending_payment' => ['badge-pending', 'Pending payment'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
    'cancelled' => ['badge-cancelled', 'Cancelled'],
    'rescheduled' => ['badge-pending', 'Rescheduled'],
];

$monthLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
$firstDayOfWeek = (int) date('N', mktime(0, 0, 0, $month, 1, $year)); // 1 = Monday .. 7 = Sunday
$today = date('Y-m-d');

$weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$maxChips = 3;

// Build a flat list of cells: leading blanks, then each day of the month,
// padded at the end so the final week is a full row of 7.
$cells = array_fill(0, $firstDayOfWeek - 1, null);
for ($day = 1; $day <= $daysInMonth; $day++) {
    $cells[] = $day;
}
while (count($cells) % 7 !== 0) {
    $cells[] = null;
}
$weeks = array_chunk($cells, 7);
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="text-align:left;margin:0 0 var(--space-6)">
        <h1 class="services-hero__title" style="margin:0">Calendar</h1>
        <p class="services-hero__subtitle" style="margin:0">Cross-instructor month view of bookings.</p>
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <a href="/admin/calendar?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-secondary btn-sm">&larr; Prev</a>
        <h2 style="font-size:var(--font-size-lg);margin:0"><?= View::e($monthLabel) ?></h2>
        <a href="/admin/calendar?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-secondary btn-sm">Next &rarr;</a>
    </div>

    <div class="card" style="padding:var(--space-4);overflow-x:auto">
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
                            <span class="badge badge-neutral" style="font-size:9px;padding:2px var(--space-2)"><?= $count ?> session<?= $count === 1 ? '' : 's' ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($count > 0): ?>
                        <div class="flex flex-col gap-1">
                            <?php foreach (array_slice($dayBookings, 0, $maxChips) as $booking):
                                [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
                                $timeLabel = date('g:i A', strtotime($booking['start_time']));
                            ?>
                            <div style="font-size:10px;line-height:1.4" title="<?= View::e($booking['package_name'] . ' — ' . $booking['client_name'] . ' with ' . $booking['instructor_name']) ?>">
                                <span class="text-muted"><?= View::e($timeLabel) ?></span>
                                <span> <?= View::e($booking['instructor_name']) ?></span>
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
</div>
