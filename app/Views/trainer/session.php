<?php

use App\Core\View;

$pageTitle = 'Session — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'bookings';

$dateLabel = date('l, d M Y', strtotime($booking['slot_date']));
$timeLabel = date('g:i A', strtotime($booking['start_time'])) . ' – ' . date('g:i A', strtotime($booking['end_time']));
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title"><?= View::e($client['name'] ?? 'Session') ?></h1>
            <p class="trainer-hero__subtitle"><?= View::e($package['name'] ?? 'Session') ?> · <?= View::e($dateLabel) ?> at <?= View::e($timeLabel) ?></p>
        </div>
        <a href="/trainer/students/<?= (int) $booking['client_id'] ?>" class="btn btn-secondary btn-sm">View student</a>
    </div>

    <?php if ($meetingLink): ?>
    <div class="card mb-8" style="padding:var(--space-5)">
        <strong>Meeting link:</strong> <a href="<?= View::e($meetingLink['url']) ?>" target="_blank" rel="noopener"><?= View::e($meetingLink['url']) ?></a>
    </div>
    <?php else: ?>
    <div class="card mb-8" style="padding:var(--space-5)">
        <span class="text-muted">Live video — Coming soon.</span>
    </div>
    <?php endif; ?>

    <h2 class="trainer-section-title">Session Notes &amp; Attendance</h2>
    <div class="card mb-8" style="padding:var(--space-6)">
        <form id="session-form">
            <div class="flex gap-3 flex-wrap mb-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="attendance" value="present" <?= ($feedback['attendance'] ?? '') === 'present' ? 'checked' : '' ?>> Present
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="attendance" value="absent" <?= ($feedback['attendance'] ?? '') === 'absent' ? 'checked' : '' ?>> Absent
                </label>
            </div>
            <div class="form-group">
                <textarea name="session_notes" class="form-group__control" placeholder=" " rows="3"><?= View::e($feedback['session_notes'] ?? '') ?></textarea>
                <label class="form-group__label">Session notes (private)</label>
            </div>
            <div class="form-group">
                <textarea name="recommendation" class="form-group__control" placeholder=" " rows="2"><?= View::e($feedback['recommendation'] ?? '') ?></textarea>
                <label class="form-group__label">Recommendations for student</label>
            </div>
            <div class="form-group">
                <textarea name="homework" class="form-group__control" placeholder=" " rows="2"><?= View::e($feedback['homework'] ?? '') ?></textarea>
                <label class="form-group__label">Homework / practice for next time</label>
            </div>
            <div class="flex gap-3 flex-wrap items-end mb-4">
                <div class="form-group" style="margin:0">
                    <select name="rating" class="form-group__control">
                        <option value="">No rating</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) ($feedback['rating'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?> / 5</option>
                        <?php endfor; ?>
                    </select>
                    <label class="form-group__label">Rate student</label>
                </div>
            </div>
            <div class="form-group">
                <textarea name="feedback_text" class="form-group__control" placeholder=" " rows="2"><?= View::e($feedback['feedback_text'] ?? '') ?></textarea>
                <label class="form-group__label">Feedback about student (optional)</label>
            </div>
            <div class="form-group__error" id="session-form-error"></div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>

    <h2 class="trainer-section-title">Resources</h2>
    <div class="card" style="padding:var(--space-6)">
        <?php if (empty($resources)): ?>
        <p class="text-muted mb-4">No resources uploaded yet.</p>
        <?php else: ?>
        <div class="flex flex-col gap-2 mb-6">
            <?php foreach ($resources as $resource): ?>
            <div class="flex items-center justify-between" style="padding:var(--space-2) 0;border-bottom:1px solid var(--border-subtle)">
                <a href="<?= View::e($resource['file_path']) ?>" target="_blank" rel="noopener"><?= View::e($resource['title']) ?></a>
                <span class="text-muted" style="font-size:var(--font-size-sm)"><?= View::e(date('d M Y', strtotime($resource['created_at']))) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form id="resource-form" class="flex gap-3 flex-wrap items-end">
            <div class="form-group" style="margin:0;flex:1;min-width:200px">
                <input type="text" name="title" class="form-group__control" placeholder=" " maxlength="200">
                <label class="form-group__label">Title (optional)</label>
            </div>
            <input type="file" name="file" required>
            <button type="submit" class="btn btn-secondary">Upload</button>
        </form>
    </div>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.getElementById('session-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const errorBox = document.getElementById('session-form-error');
    errorBox.textContent = '';

    const attendance = form.querySelector('[name="attendance"]:checked')?.value ?? '';

    try {
        await apiPost('<?= View::e('/trainer/sessions/' . $booking['booking_ref']) ?>', {
            attendance,
            session_notes: form.session_notes.value.trim(),
            recommendation: form.recommendation.value.trim(),
            homework: form.homework.value.trim(),
            rating: form.rating.value,
            feedback_text: form.feedback_text.value.trim(),
        });
        toast.success('Session saved.');
    } catch (err) {
        errorBox.textContent = err.message;
        toast.error(err.message);
    }
});

document.getElementById('resource-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const response = await fetch('<?= View::e('/trainer/sessions/' . $booking['booking_ref'] . '/resources') ?>', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-Token': csrf },
            body: formData,
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Upload failed.');
        window.location.reload();
    } catch (err) {
        toast.error(err.message);
    }
});
</script>
