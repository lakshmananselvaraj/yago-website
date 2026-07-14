<?php

use App\Core\View;

$pageTitle = 'Notifications — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'notifications';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16);max-width:760px">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">Notifications</h1>
            <p class="services-hero__subtitle" style="margin:0">Send an in-app notification to any user and review recent sends.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="add-notification-btn">+ Send Notification</button>
    </div>

    <?php if (empty($notifications)): ?>
    <p class="text-muted">No notifications sent yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-3">
        <?php foreach ($notifications as $notification): ?>
        <div class="card" style="padding:var(--space-4) var(--space-5)">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                <div>
                    <strong><?= View::e($notification['title']) ?></strong>
                    <span class="text-muted"> to <?= View::e($notification['user_name'] ?? 'Unknown user') ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($notification['is_read']): ?>
                    <span class="badge badge-confirmed">Read</span>
                    <?php else: ?>
                    <span class="badge badge-pending">Unread</span>
                    <?php endif; ?>
                    <span class="text-muted" style="font-size:var(--font-size-sm)"><?= date('d M Y, g:i A', strtotime($notification['created_at'])) ?></span>
                </div>
            </div>
            <?php if (!empty($notification['body'])): ?>
            <p class="text-muted" style="font-size:var(--font-size-sm);margin:0"><?= View::e($notification['body']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="notification-modal-overlay" style="display:none">
    <div class="modal modal-glass">
        <div class="modal__header">
            <div class="modal__title">Send Notification</div>
            <button type="button" class="modal__close" id="notification-modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="notification-form">
            <div class="modal__body flex flex-col gap-4">
                <div class="form-group">
                    <select name="target" id="notification-target" class="form-group__control">
                        <option value="user">Specific user</option>
                        <option value="all_clients">All clients</option>
                        <option value="all_trainers">All trainers</option>
                    </select>
                    <label class="form-group__label">Send to</label>
                </div>
                <div class="form-group" id="notification-user-group">
                    <select name="user_id" id="notification-user" class="form-group__control">
                        <option value="" disabled selected>Select a user…</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= (int) $user['id'] ?>"><?= View::e($user['name']) ?> (<?= View::e($user['email'] ?? $user['role']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-group__label">User</label>
                </div>
                <div class="form-group">
                    <input type="text" name="title" class="form-group__control" placeholder=" " required maxlength="200">
                    <label class="form-group__label">Title</label>
                </div>
                <div class="form-group">
                    <textarea name="body" class="form-group__control" placeholder=" " rows="3"></textarea>
                    <label class="form-group__label">Message (optional)</label>
                </div>
                <div class="form-group__error" id="notification-form-error"></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" id="notification-modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="notification-submit-btn">Send</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

const overlay = document.getElementById('notification-modal-overlay');
const form = document.getElementById('notification-form');
const submitBtn = document.getElementById('notification-submit-btn');
const errorBox = document.getElementById('notification-form-error');

function openModal() {
    form.reset();
    errorBox.textContent = '';
    overlay.style.display = 'flex';
}

function closeModal() {
    overlay.style.display = 'none';
}

document.getElementById('add-notification-btn').addEventListener('click', openModal);
document.getElementById('notification-modal-close').addEventListener('click', closeModal);
document.getElementById('notification-modal-cancel').addEventListener('click', closeModal);
overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

const targetSelect = document.getElementById('notification-target');
const userGroup = document.getElementById('notification-user-group');

function syncUserGroup() {
    const isSpecificUser = targetSelect.value === 'user';
    userGroup.style.display = isSpecificUser ? '' : 'none';
    form.user_id.required = isSpecificUser;
}
targetSelect.addEventListener('change', syncUserGroup);
syncUserGroup();

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorBox.textContent = '';
    submitBtn.classList.add('is-loading');
    submitBtn.disabled = true;

    const target = form.target.value;
    const payload = {
        target,
        title: form.title.value.trim(),
        body: form.body.value.trim(),
    };
    if (target === 'user') {
        payload.user_id = Number(form.user_id.value);
    }

    try {
        const result = await apiPost('/admin/notifications', payload);
        toast.success(result.message);
        window.location.reload();
    } catch (err) {
        errorBox.textContent = err.message;
        toast.error(err.message);
    } finally {
        submitBtn.classList.remove('is-loading');
        submitBtn.disabled = false;
    }
});
</script>
