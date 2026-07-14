<?php

use App\Core\View;

$pageTitle = 'Manage Customers — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'admin';
$active = 'customers';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero">
        <h1 class="services-hero__title">Manage Customers</h1>
        <p class="services-hero__subtitle">Browse registered clients, their bookings and payment history.</p>
    </div>

    <form method="get" action="/admin/customers" class="flex gap-3 flex-wrap items-center mb-4" style="max-width:820px;margin-inline:auto">
        <input type="text" name="q" class="form-group__control" style="flex:1;min-width:220px" placeholder="Search by name or email…" value="<?= View::e($q ?? '') ?>">
        <input type="hidden" name="status" value="<?= View::e($status ?? '') ?>">
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if ($q): ?>
        <a href="/admin/customers<?= ($status ?? '') !== '' ? '?status=' . urlencode($status) : '' ?>" class="chip">Clear</a>
        <?php endif; ?>
    </form>

    <div class="services-filters mb-8" style="max-width:820px;margin-inline:auto">
        <a href="/admin/customers<?= $q ? '?q=' . urlencode($q) : '' ?>" class="chip<?= ($status ?? '') === '' ? ' is-selected' : '' ?>">Active customers</a>
        <a href="/admin/customers?status=test<?= $q ? '&q=' . urlencode($q) : '' ?>" class="chip<?= ($status ?? '') === 'test' ? ' is-selected' : '' ?>">Test accounts</a>
        <a href="/admin/customers?status=all<?= $q ? '&q=' . urlencode($q) : '' ?>" class="chip<?= ($status ?? '') === 'all' ? ' is-selected' : '' ?>">All</a>
    </div>

    <?php if (empty($customers)): ?>
    <p class="text-muted text-center">No customers match this search.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:820px;margin-inline:auto">
        <?php foreach ($customers as $customer): ?>
        <div class="card" style="padding:var(--space-5)">
            <div class="flex items-center justify-between mb-2">
                <div style="font-weight:var(--font-weight-semibold)">
                    <?= View::e($customer['name']) ?>
                    <?php if ($customer['status'] === 'test'): ?>
                    <span class="badge badge-pending" style="margin-left:var(--space-2)">TEST</span>
                    <?php endif; ?>
                </div>
                <a href="/admin/customers/<?= (int) $customer['id'] ?>" class="btn btn-secondary btn-sm">View</a>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm)">
                <?= View::e($customer['email'] ?: '—') ?> · <?= View::e($customer['phone'] ?: '—') ?> · <?= View::e($customer['country'] ?: '—') ?>
            </p>
            <p class="text-muted" style="font-size:var(--font-size-sm);margin-top:var(--space-2)">
                <?= (int) $customer['booking_count'] ?> booking<?= (int) $customer['booking_count'] === 1 ? '' : 's' ?>
                · Total paid <?= number_format((float) $customer['total_paid'], 2) ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-3 mt-8">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="/admin/customers?page=<?= $p ?><?= $q ? '&q=' . urlencode($q) : '' ?><?= ($status ?? '') !== '' ? '&status=' . urlencode($status) : '' ?>" class="chip<?= $p === $page ? ' is-selected' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
