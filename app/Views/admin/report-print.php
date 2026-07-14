<?php

use App\Core\View;

$typeLabels = [
    'revenue' => 'Revenue Report',
    'bookings' => 'Bookings Report',
    'payments' => 'Payments Report',
    'instructor_performance' => 'Instructor Performance Report',
];
$title = $typeLabels[$type] ?? 'Report';

$pageTitle = $title . ' — Vipasa Yoga';
$pageCss = 'booking';
?>
<div class="container" style="max-width:960px;padding-block:var(--space-10)">
    <button type="button" class="btn btn-secondary no-print mb-6" onclick="window.print()">Print / Save as PDF</button>

    <div class="card" style="padding:var(--space-8)">
        <div class="flex items-center justify-between mb-6">
            <div class="splash__wordmark">Vipasa Yoga</div>
            <div class="text-right">
                <div style="font-weight:var(--font-weight-semibold)"><?= View::e($title) ?></div>
                <div class="text-muted" style="font-size:var(--font-size-sm)"><?= View::e($from) ?> to <?= View::e($to) ?></div>
            </div>
        </div>

        <?php if (empty($rows)): ?>
        <p class="text-muted">No data for this date range.</p>
        <?php else: ?>
        <div style="overflow-x:auto">
            <table style="width:100%;font-size:var(--font-size-sm)">
                <thead>
                    <tr>
                        <?php foreach ($headers as $h): ?>
                        <th style="text-align:left;padding:var(--space-2) var(--space-3);border-bottom:2px solid var(--border-default)"><?= View::e($h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                        <td style="padding:var(--space-2) var(--space-3);border-bottom:1px solid var(--border-subtle)"><?= View::e((string) $cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
