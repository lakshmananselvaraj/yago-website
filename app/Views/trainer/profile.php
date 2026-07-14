<?php

use App\Core\View;

$pageTitle = 'My Profile — Vipasa Yoga';
$pageCss = 'trainer';
$portal = 'trainer';
$active = 'profile';
$specialties = $instructor['specialties'] ?? [];
$certificates = $instructor['certificates'] ?? [];
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">

    <div class="trainer-hero">
        <div>
            <h1 class="trainer-hero__title">My Profile</h1>
            <p class="trainer-hero__subtitle">This is what clients see when booking with you.</p>
        </div>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <div class="flex items-center gap-4 mb-6 flex-wrap">
            <img src="<?= View::e($instructor['avatar_path'] ?: '/assets/img/avatar-placeholder.svg') ?>" alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover">
            <div>
                <label class="btn btn-secondary btn-sm" style="cursor:pointer">
                    Change photo
                    <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/webp" style="display:none">
                </label>
            </div>
        </div>

        <form id="profile-form">
            <div class="form-group">
                <input type="text" name="name" class="form-group__control" placeholder=" " required maxlength="150" value="<?= View::e($user['name'] ?? '') ?>">
                <label class="form-group__label">Name</label>
            </div>
            <div class="form-group">
                <input type="text" name="phone" class="form-group__control" placeholder=" " maxlength="20" value="<?= View::e($user['phone'] ?? '') ?>">
                <label class="form-group__label">Phone</label>
            </div>
            <div class="form-group">
                <input type="text" name="headline" class="form-group__control" placeholder=" " maxlength="200" value="<?= View::e($instructor['headline'] ?? '') ?>">
                <label class="form-group__label">Headline</label>
            </div>
            <div class="form-group">
                <textarea name="bio" class="form-group__control" placeholder=" " rows="4"><?= View::e($instructor['bio'] ?? '') ?></textarea>
                <label class="form-group__label">Biography</label>
            </div>
            <div class="flex gap-3 flex-wrap mb-4">
                <div class="form-group" style="flex:1;min-width:140px;margin:0">
                    <input type="number" name="experience_years" class="form-group__control" placeholder=" " min="0" value="<?= (int) ($instructor['experience_years'] ?? 0) ?>">
                    <label class="form-group__label">Experience (years)</label>
                </div>
                <div class="form-group" style="flex:1;min-width:160px;margin:0">
                    <input type="text" name="timezone" class="form-group__control" placeholder=" " required value="<?= View::e($instructor['timezone'] ?? 'UTC') ?>">
                    <label class="form-group__label">Timezone</label>
                </div>
            </div>
            <div class="form-group">
                <input type="text" name="specialties" class="form-group__control" placeholder=" " value="<?= View::e(implode(', ', $specialties ?: [])) ?>">
                <label class="form-group__label">Specialties (comma-separated)</label>
            </div>
            <div class="form-group">
                <input type="text" name="certificates" class="form-group__control" placeholder=" " value="<?= View::e(implode(', ', $certificates ?: [])) ?>">
                <label class="form-group__label">Certificates (comma-separated)</label>
            </div>
            <div class="form-group__error" id="profile-form-error"></div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    <h2 class="trainer-section-title">My Gallery</h2>
    <div class="card" style="padding:var(--space-6)">
        <div class="trainer-gallery mb-4" id="trainer-gallery">
            <?php foreach ($gallery as $image): ?>
            <div class="trainer-gallery__item" data-id="<?= (int) $image['id'] ?>">
                <img src="<?= View::e($image['file_path']) ?>" alt="">
                <button type="button" class="btn btn-ghost btn-sm trainer-gallery__remove delete-gallery-btn" data-id="<?= (int) $image['id'] ?>">&times;</button>
            </div>
            <?php endforeach; ?>
        </div>
        <label class="btn btn-secondary" style="cursor:pointer">
            + Add Photo
            <input type="file" id="gallery-input" accept="image/jpeg,image/png,image/webp" style="display:none">
        </label>
    </div>

    <h2 class="trainer-section-title">Certificate Files</h2>
    <div class="card" style="padding:var(--space-6)">
        <?php if (empty($certificateFiles)): ?>
        <p class="text-muted mb-4">No certificate files uploaded yet.</p>
        <?php else: ?>
        <div class="flex flex-col gap-2 mb-4" id="certificate-files-list">
            <?php foreach ($certificateFiles as $file): ?>
            <div class="flex items-center justify-between" style="padding:var(--space-2) 0;border-bottom:1px solid var(--border-subtle)" data-id="<?= (int) $file['id'] ?>">
                <a href="<?= View::e($file['file_path']) ?>" target="_blank" rel="noopener"><?= View::e($file['title']) ?></a>
                <button type="button" class="btn btn-ghost btn-sm delete-certificate-btn" data-id="<?= (int) $file['id'] ?>">Remove</button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form id="certificate-form" class="flex gap-3 flex-wrap items-end">
            <div class="form-group" style="margin:0;flex:1;min-width:200px">
                <input type="text" name="title" class="form-group__control" placeholder=" " maxlength="200">
                <label class="form-group__label">Title (optional)</label>
            </div>
            <input type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" required>
            <button type="submit" class="btn btn-secondary">Upload</button>
        </form>
    </div>
</div>
<script type="module">
import { apiPut, apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.getElementById('profile-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const errorBox = document.getElementById('profile-form-error');
    errorBox.textContent = '';

    const payload = {
        name: form.name.value.trim(),
        phone: form.phone.value.trim(),
        headline: form.headline.value.trim(),
        bio: form.bio.value.trim(),
        experience_years: Number(form.experience_years.value || 0),
        timezone: form.timezone.value.trim(),
        specialties: form.specialties.value.trim(),
        certificates: form.certificates.value.trim(),
    };

    try {
        await apiPut('/trainer/profile', payload);
        toast.success('Profile saved.');
    } catch (err) {
        errorBox.textContent = err.message;
        toast.error(err.message);
    }
});

async function uploadFile(url, fieldName, file) {
    const formData = new FormData();
    formData.append(fieldName, file);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-CSRF-Token': csrf },
        body: formData,
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'Upload failed.');
    return data;
}

document.getElementById('avatar-input').addEventListener('change', async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    try {
        await uploadFile('/trainer/profile/avatar', 'avatar', file);
        window.location.reload();
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('gallery-input').addEventListener('change', async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    try {
        await uploadFile('/trainer/profile/gallery', 'image', file);
        window.location.reload();
    } catch (err) {
        toast.error(err.message);
    }
});

document.querySelectorAll('.delete-gallery-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        try {
            await apiPost(`/trainer/profile/gallery/${btn.dataset.id}/delete`, {});
            btn.closest('[data-id]').remove();
        } catch (err) {
            toast.error(err.message);
        }
    });
});

document.getElementById('certificate-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const response = await fetch('/trainer/profile/certificates', {
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

document.querySelectorAll('.delete-certificate-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        try {
            await apiPost(`/trainer/profile/certificates/${btn.dataset.id}/delete`, {});
            btn.closest('[data-id]').remove();
        } catch (err) {
            toast.error(err.message);
        }
    });
});
</script>
