<?php

use App\Core\View;

$pageTitle = 'My Students — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'students';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">Students</h1>
            <p class="trainer-hero__subtitle">Everyone who has booked a confirmed session with you.</p>
        </div>
    </div>

    <?php if (empty($students)): ?>
    <p class="text-muted text-center">No students yet.</p>
    <?php else: ?>
    <div class="trainer-schedule">
        <?php foreach ($students as $student): ?>
        <div class="trainer-schedule-row">
            <div>
                <strong><?= View::e($student['name']) ?></strong>
                <span class="text-muted" style="font-size:var(--font-size-sm)"> · <?= (int) $student['session_count'] ?> session(s) · Last <?= View::e(date('d M Y', strtotime($student['last_session']))) ?></span>
            </div>
            <a href="/trainer/students/<?= (int) $student['id'] ?>" class="btn btn-secondary btn-sm">View</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
