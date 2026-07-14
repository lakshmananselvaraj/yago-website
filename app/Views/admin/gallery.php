<?php

use App\Core\View;

$pageTitle = 'Gallery — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'gallery';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="margin:0 0 var(--space-6);text-align:left">
        <h1 class="services-hero__title" style="margin:0">Gallery</h1>
        <p class="services-hero__subtitle" style="margin:0">Photos shown on the public /gallery page and homepage preview.</p>
    </div>

    <div class="card mb-8" style="padding:var(--space-6)">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Upload Photo</h2>
        <form id="gallery-upload-form" class="flex gap-3 flex-wrap items-end">
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
            <div class="form-group" style="margin:0;flex:1;min-width:160px">
                <input type="text" name="caption" class="form-group__control" placeholder=" " maxlength="200">
                <label class="form-group__label">Caption</label>
            </div>
            <div class="form-group" style="margin:0;min-width:140px">
                <input type="text" name="category" class="form-group__control" placeholder=" " maxlength="60">
                <label class="form-group__label">Category</label>
            </div>
            <div class="form-group" style="margin:0;max-width:100px">
                <input type="number" name="sort_order" class="form-group__control" value="0">
                <label class="form-group__label">Order</label>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        <div class="form-group__error" id="gallery-upload-error"></div>
    </div>

    <?php if (empty($images)): ?>
    <p class="text-muted text-center">No photos yet — upload one above.</p>
    <?php else: ?>
    <div class="service-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
        <?php foreach ($images as $image): ?>
        <div class="card" style="padding:var(--space-4)<?= (int) $image['is_active'] === 0 ? ';opacity:0.55' : '' ?>" data-image-row data-id="<?= (int) $image['id'] ?>">
            <img src="<?= View::e($image['file_path']) ?>" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--radius-md);margin-bottom:var(--space-3)">
            <div class="form-group" style="margin:0 0 var(--space-2)">
                <input type="text" data-field="caption" class="form-group__control" placeholder="Caption" value="<?= View::e($image['caption'] ?? '') ?>">
            </div>
            <div class="flex gap-2 mb-2">
                <input type="text" data-field="category" class="form-group__control" placeholder="Category" style="flex:1" value="<?= View::e($image['category'] ?? '') ?>">
                <input type="number" data-field="sort_order" class="form-group__control" style="max-width:70px" value="<?= (int) $image['sort_order'] ?>">
            </div>
            <label class="flex items-center gap-2 mb-2" style="font-size:var(--font-size-sm)">
                <input type="checkbox" data-field="is_active" <?= (int) $image['is_active'] === 1 ? 'checked' : '' ?>>
                Visible on public gallery
            </label>
            <div class="flex gap-2">
                <button type="button" class="btn btn-secondary btn-sm save-image-btn">Save</button>
                <button type="button" class="btn btn-ghost btn-sm delete-image-btn">Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script type="module">
import { apiPut, apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

document.getElementById('gallery-upload-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const errorBox = document.getElementById('gallery-upload-error');
    errorBox.textContent = '';

    const formData = new FormData(form);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const response = await fetch('/admin/gallery', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-Token': csrf },
            body: formData,
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Upload failed.');
        window.location.reload();
    } catch (err) {
        errorBox.textContent = err.message;
        toast.error(err.message);
    }
});

document.querySelectorAll('.save-image-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const row = btn.closest('[data-image-row]');
        const id = row.dataset.id;
        try {
            await apiPut(`/admin/gallery/${id}`, {
                caption: row.querySelector('[data-field="caption"]').value.trim(),
                category: row.querySelector('[data-field="category"]').value.trim(),
                sort_order: Number(row.querySelector('[data-field="sort_order"]').value || 0),
                is_active: row.querySelector('[data-field="is_active"]').checked,
            });
            row.style.opacity = row.querySelector('[data-field="is_active"]').checked ? '1' : '0.55';
            toast.success('Saved.');
        } catch (err) {
            toast.error(err.message);
        }
    });
});

document.querySelectorAll('.delete-image-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this photo?')) return;
        const row = btn.closest('[data-image-row]');
        try {
            await apiPost(`/admin/gallery/${row.dataset.id}/delete`, {});
            row.remove();
        } catch (err) {
            toast.error(err.message);
        }
    });
});
</script>
