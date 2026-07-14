<?php

use App\Core\View;

$pageTitle = 'My Dashboard — Vipasa Yoga';
$pageCss = 'booking';
$portal = 'client';
$active = 'dashboard';
$user = \App\Core\Auth::user();
$firstName = explode(' ', $user['name'] ?? 'there')[0];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="margin:0 0 var(--space-6);text-align:left">
        <h1 class="services-hero__title" style="margin:0">Welcome back, <?= View::e($firstName) ?></h1>
        <p class="services-hero__subtitle" style="margin:0;font-style:italic">"<?= View::e($wellnessQuote) ?>"</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:var(--space-4);margin-bottom:var(--space-8)">
        <div class="card" style="padding:var(--space-5);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Upcoming sessions</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $upcomingCount ?></div>
        </div>
        <div class="card" style="padding:var(--space-5);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Completed classes</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $completedCount ?></div>
        </div>
        <div class="card" style="padding:var(--space-5);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Favorite trainers</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $favoriteCount ?></div>
        </div>
        <div class="card" style="padding:var(--space-5);text-align:center">
            <div class="text-muted" style="font-size:var(--font-size-sm)">Weekly streak</div>
            <div style="font-size:var(--font-size-2xl);font-weight:var(--font-weight-bold)"><?= (int) $weekStreak ?> <?= $weekStreak === 1 ? 'week' : 'weeks' ?></div>
        </div>
    </div>

    <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Next Session</h2>
    <?php if ($nextSession === null): ?>
    <div class="card mb-8" style="padding:var(--space-6);text-align:center">
        <p class="text-muted mb-4">You don't have an upcoming session yet.</p>
        <a href="/services" class="btn btn-accent">Book a Session</a>
    </div>
    <?php else:
        $dateLabel = date('l, d M Y', strtotime($nextSession['slot_date']));
        $timeLabel = date('g:i A', strtotime($nextSession['start_time']));
    ?>
    <div class="card mb-8" style="padding:var(--space-6)">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div style="font-weight:var(--font-weight-semibold)"><?= View::e($nextSession['package']['name'] ?? 'Session') ?></div>
                <p class="text-muted" style="margin:0">With <?= View::e($nextSession['instructor']['name'] ?? 'your instructor') ?> · <?= View::e($dateLabel) ?> at <?= View::e($timeLabel) ?></p>
            </div>
            <a href="/dashboard/bookings" class="btn btn-secondary btn-sm">View booking</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex gap-3 flex-wrap">
        <a href="/services" class="btn btn-accent">Book a Session</a>
        <a href="/instructors" class="btn btn-secondary">Browse Instructors</a>
        <a href="/dashboard/bookings" class="btn btn-secondary">My Bookings</a>
    </div>
</div>
