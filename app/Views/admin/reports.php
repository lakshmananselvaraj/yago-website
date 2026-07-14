<?php

use App\Core\View;

$pageTitle = 'Reports — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'reports';

$reportTypes = [
    'revenue' => 'Revenue (daily)',
    'bookings' => 'Bookings',
    'payments' => 'Payments',
    'instructor_performance' => 'Instructor performance',
];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16);max-width:820px">
    <div class="services-hero" style="text-align:left;margin:0 0 var(--space-8)">
        <h1 class="services-hero__title" style="margin:0">Reports</h1>
        <p class="services-hero__subtitle" style="margin:0">Download revenue, booking, payment, and instructor performance reports for any date range.</p>
    </div>

    <div class="card" style="padding:var(--space-6);margin-bottom:var(--space-6)">
        <form id="range-form" class="flex items-end gap-4 flex-wrap">
            <div class="form-group" style="margin:0">
                <input type="date" id="from" name="from" class="form-group__control" value="<?= View::e($from) ?>">
                <label class="form-group__label" for="from">From</label>
            </div>
            <div class="form-group" style="margin:0">
                <input type="date" id="to" name="to" class="form-group__control" value="<?= View::e($to) ?>">
                <label class="form-group__label" for="to">To</label>
            </div>
            <button type="submit" class="btn btn-secondary">Update range</button>
        </form>
    </div>

    <div class="flex flex-col gap-4">
        <?php foreach ($reportTypes as $key => $label): ?>
        <div class="card flex items-center justify-between flex-wrap gap-3" style="padding:var(--space-5)">
            <div style="font-weight:var(--font-weight-medium)"><?= View::e($label) ?></div>
            <div class="flex gap-3">
                <a class="btn btn-secondary btn-sm" href="/admin/reports/export?type=<?= $key ?>&format=print&from=<?= View::e($from) ?>&to=<?= View::e($to) ?>" target="_blank" rel="noopener">View / Print</a>
                <a class="btn btn-secondary btn-sm" href="/admin/reports/export?type=<?= $key ?>&format=csv&from=<?= View::e($from) ?>&to=<?= View::e($to) ?>">CSV</a>
                <a class="btn btn-secondary btn-sm" href="/admin/reports/export?type=<?= $key ?>&format=xlsx&from=<?= View::e($from) ?>&to=<?= View::e($to) ?>">Excel</a>
                <a class="btn btn-primary btn-sm" href="/admin/reports/export?type=<?= $key ?>&format=pdf&from=<?= View::e($from) ?>&to=<?= View::e($to) ?>">PDF</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script type="module">
document.getElementById('range-form').addEventListener('submit', (event) => {
    event.preventDefault();
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;
    window.location.href = `/admin/reports?from=${from}&to=${to}`;
});
</script>
