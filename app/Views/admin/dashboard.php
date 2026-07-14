<?php

use App\Core\View;

$pageTitle = 'Admin Dashboard — Vipasa Yoga';
$pageCss = 'services';
$portal = 'admin';
$active = 'dashboard';

$statusMeta = [
    'pending_payment' => ['badge-pending', 'Pending payment'],
    'confirmed' => ['badge-confirmed', 'Confirmed'],
    'completed' => ['badge-completed', 'Completed'],
    'cancelled' => ['badge-cancelled', 'Cancelled'],
    'rescheduled' => ['badge-pending', 'Rescheduled'],
];

$chartData = [
    'revenue' => $revenueSeries,
    'bookings' => $bookingsSeries,
    'userGrowth' => $userGrowthSeries,
    'packageSales' => [
        'labels' => array_column($packageSales, 'label'),
        'values' => array_map('intval', array_column($packageSales, 'value')),
    ],
    'popularInstructors' => [
        'labels' => array_column($popularInstructors, 'label'),
        'values' => array_map('intval', array_column($popularInstructors, 'value')),
    ],
    'paymentsByGateway' => [
        'labels' => array_map('ucfirst', array_column($paymentsByGateway, 'label')),
        'values' => array_map('intval', array_column($paymentsByGateway, 'value')),
    ],
    'paymentsByStatus' => [
        'labels' => array_map('ucfirst', array_column($paymentsByStatus, 'label')),
        'values' => array_map('intval', array_column($paymentsByStatus, 'value')),
    ],
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="text-align:left;margin:0 0 var(--space-6)">
        <h1 class="services-hero__title" style="margin:0">Admin Dashboard</h1>
        <p class="services-hero__subtitle" style="margin:0">Last 30 days of activity.</p>
    </div>

    <div class="service-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:var(--space-8)">
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Today's bookings</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $todayCount ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Confirmed bookings</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $confirmedCount ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Awaiting payment</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $pendingCount ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Revenue</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)">₹<?= number_format((float) $revenue, 2) ?></div>
        </div>
    </div>

    <div class="service-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:var(--space-8)">
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Total clients</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $totalClients ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Total trainers</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $totalTrainers ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Active packages</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $activePackages ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Today's revenue</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)">₹<?= number_format($todayRevenue, 2) ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">This month's revenue</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)">₹<?= number_format($monthRevenue, 2) ?></div>
        </div>
        <div class="card" style="padding:var(--space-6);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Pending payments</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)">₹<?= number_format($pendingPaymentsAmount, 2) ?></div>
        </div>
    </div>

    <div class="service-grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));margin-bottom:var(--space-8)">
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">Revenue (last 30 days)</div>
            <div id="chart-revenue"></div>
        </div>
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">Bookings (last 30 days)</div>
            <div id="chart-bookings"></div>
        </div>
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">New clients (last 30 days)</div>
            <div id="chart-user-growth"></div>
        </div>
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">Package sales</div>
            <div id="chart-package-sales"></div>
        </div>
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">Popular instructors</div>
            <div id="chart-popular-instructors"></div>
        </div>
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">Payments by gateway</div>
            <div id="chart-payments-gateway"></div>
        </div>
        <div class="card" style="padding:var(--space-6)">
            <div class="chart-card__title">Payments by status</div>
            <div id="chart-payments-status"></div>
        </div>
    </div>

    <?php
    $sections = [
        ['title' => "Today's Schedule", 'rows' => $todaysSchedule, 'empty' => 'Nothing scheduled today.'],
        ['title' => 'Upcoming Sessions', 'rows' => $upcoming, 'empty' => 'No upcoming confirmed sessions.'],
        ['title' => 'Cancelled Sessions', 'rows' => $cancelled, 'empty' => 'No cancellations.'],
    ];
    ?>
    <?php foreach ($sections as $section): ?>
    <div class="mb-8">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)"><?= View::e($section['title']) ?></h2>
        <?php if (empty($section['rows'])): ?>
        <p class="text-muted"><?= View::e($section['empty']) ?></p>
        <?php else: ?>
        <div class="flex flex-col gap-3">
            <?php foreach ($section['rows'] as $booking):
                [$badgeClass, $badgeLabel] = $statusMeta[$booking['status']] ?? ['badge-neutral', ucfirst($booking['status'])];
                $dateLabel = date('d M Y', strtotime($booking['slot_date']));
                $timeLabel = date('g:i A', strtotime($booking['start_time']));
            ?>
            <div class="card flex items-center justify-between flex-wrap gap-3" style="padding:var(--space-4) var(--space-5)">
                <div>
                    <strong><?= View::e($booking['client_name']) ?></strong>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= View::e($booking['package_name']) ?> with <?= View::e($booking['instructor_name']) ?> · <?= View::e($dateLabel) ?> <?= View::e($timeLabel) ?></span>
                </div>
                <span class="badge <?= $badgeClass ?>"><?= View::e($badgeLabel) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<script type="application/json" id="admin-chart-data"><?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script type="module">
import { renderLineChart, renderBarChart } from '/assets/js/modules/charts.js';

const data = JSON.parse(document.getElementById('admin-chart-data').textContent);

renderLineChart(document.getElementById('chart-revenue'), data.revenue, { color: 'var(--color-primary)' });
renderBarChart(document.getElementById('chart-bookings'), data.bookings, { color: 'var(--color-accent)' });
renderLineChart(document.getElementById('chart-user-growth'), data.userGrowth, { color: 'var(--color-tertiary)' });
renderBarChart(document.getElementById('chart-package-sales'), data.packageSales, { color: 'var(--color-primary)', emptyMessage: 'No confirmed bookings yet.' });
renderBarChart(document.getElementById('chart-popular-instructors'), data.popularInstructors, { color: 'var(--color-accent)', emptyMessage: 'No confirmed bookings yet.' });
renderBarChart(document.getElementById('chart-payments-gateway'), data.paymentsByGateway, { color: 'var(--color-primary)', emptyMessage: 'No successful payments yet.' });
renderBarChart(document.getElementById('chart-payments-status'), data.paymentsByStatus, { color: 'var(--color-accent)', emptyMessage: 'No payments yet.' });
</script>
