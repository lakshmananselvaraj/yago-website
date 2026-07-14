<?php

use App\Core\View;

$pageTitle = 'Manage Packages — Vipasa Yoga';
$pageCss = 'services';
$portal = 'admin';
$active = 'packages';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">Manage Packages</h1>
            <p class="services-hero__subtitle" style="margin:0">Only active + featured packages show on the public Services page first.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="add-package-btn">+ Add Package</button>
    </div>

    <?php if (empty($packages)): ?>
    <p class="text-muted text-center">No packages yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:820px;margin-inline:auto">
        <?php foreach ($packages as $package): ?>
        <div class="card" style="padding:var(--space-5)" data-package='<?= View::e(json_encode($package)) ?>'>
            <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <div style="font-weight:var(--font-weight-semibold)">
                    <?= View::e($package['name']) ?>
                    <?php if ($package['is_featured']): ?><span class="badge badge-confirmed">Featured</span><?php endif; ?>
                    <?php if (!$package['is_active']): ?><span class="badge badge-cancelled">Inactive</span><?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-ghost btn-sm edit-package-btn">Edit</button>
                    <button type="button" class="btn btn-ghost btn-sm toggle-active-btn" data-id="<?= (int) $package['id'] ?>">
                        <?= $package['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                </div>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm);margin:0">
                <?= View::e($package['service_type_name']) ?> · <?= (int) $package['sessions_count'] ?> session(s) · <?= (int) $package['duration_minutes'] ?> min
                · up to <?= (int) $package['max_participants'] ?> participant(s) · <?= View::e($package['currency']) ?> <?= number_format((float) $package['price'], 2) ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="package-modal-overlay" style="display:none">
    <div class="modal modal-glass">
        <div class="modal__header">
            <div class="modal__title" id="package-modal-title">Add Package</div>
            <button type="button" class="modal__close" id="package-modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="package-form">
            <input type="hidden" name="id" value="">
            <div class="modal__body flex flex-col gap-4">
                <div class="form-group">
                    <select name="service_type_id" id="package-service-type" class="form-group__control" required>
                        <?php foreach ($serviceTypes as $serviceType): ?>
                        <option value="<?= (int) $serviceType['id'] ?>"><?= View::e($serviceType['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-group__label">Service type</label>
                </div>
                <div class="form-group">
                    <input type="text" name="name" class="form-group__control" placeholder=" " required maxlength="150">
                    <label class="form-group__label">Package name</label>
                </div>
                <div class="form-group">
                    <textarea name="description" class="form-group__control" placeholder=" " rows="2"></textarea>
                    <label class="form-group__label">Description (optional)</label>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <div class="form-group" style="flex:1;min-width:120px">
                        <input type="number" name="sessions_count" class="form-group__control" placeholder=" " required min="1" value="1">
                        <label class="form-group__label">Sessions</label>
                    </div>
                    <div class="form-group" style="flex:1;min-width:120px">
                        <input type="number" name="duration_minutes" class="form-group__control" placeholder=" " required min="1" value="60">
                        <label class="form-group__label">Duration (min)</label>
                    </div>
                    <div class="form-group" style="flex:1;min-width:120px">
                        <input type="number" name="max_participants" class="form-group__control" placeholder=" " required min="1" value="1">
                        <label class="form-group__label">Max participants</label>
                    </div>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <div class="form-group" style="flex:1;min-width:120px">
                        <input type="number" name="price" class="form-group__control" placeholder=" " required min="0" step="0.01">
                        <label class="form-group__label">Price</label>
                    </div>
                    <div class="form-group" style="flex:1;min-width:120px">
                        <input type="text" name="currency" class="form-group__control" placeholder=" " required maxlength="3" value="INR" style="text-transform:uppercase">
                        <label class="form-group__label">Currency</label>
                    </div>
                </div>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_active" checked> Active</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_featured"> Featured (shows on public Services page)</label>
                </div>
                <div class="form-group__error" id="package-form-error"></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" id="package-modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="package-submit-btn">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
import { apiPost, apiPut } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

const overlay = document.getElementById('package-modal-overlay');
const form = document.getElementById('package-form');
const title = document.getElementById('package-modal-title');
const submitBtn = document.getElementById('package-submit-btn');
const errorBox = document.getElementById('package-form-error');

function openModal(pkg) {
    form.reset();
    errorBox.textContent = '';
    if (pkg) {
        title.textContent = 'Edit Package';
        form.id.value = pkg.id;
        form.service_type_id.value = pkg.service_type_id;
        form.name.value = pkg.name;
        form.description.value = pkg.description || '';
        form.sessions_count.value = pkg.sessions_count;
        form.duration_minutes.value = pkg.duration_minutes;
        form.max_participants.value = pkg.max_participants;
        form.price.value = pkg.price;
        form.currency.value = pkg.currency;
        form.is_active.checked = !!Number(pkg.is_active);
        form.is_featured.checked = !!Number(pkg.is_featured);
    } else {
        title.textContent = 'Add Package';
        form.id.value = '';
    }
    overlay.style.display = 'flex';
}

function closeModal() {
    overlay.style.display = 'none';
}

document.getElementById('add-package-btn').addEventListener('click', () => openModal(null));
document.getElementById('package-modal-close').addEventListener('click', closeModal);
document.getElementById('package-modal-cancel').addEventListener('click', closeModal);
overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

document.querySelectorAll('.edit-package-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const pkg = JSON.parse(btn.closest('[data-package]').dataset.package);
        openModal(pkg);
    });
});

document.querySelectorAll('.toggle-active-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/admin/packages/${btn.dataset.id}/toggle-active`, {});
            window.location.reload();
        } catch (err) {
            toast.error(err.message);
            btn.classList.remove('is-loading');
            btn.disabled = false;
        }
    });
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorBox.textContent = '';
    submitBtn.classList.add('is-loading');
    submitBtn.disabled = true;

    const payload = {
        service_type_id: Number(form.service_type_id.value),
        name: form.name.value.trim(),
        description: form.description.value.trim(),
        sessions_count: Number(form.sessions_count.value),
        duration_minutes: Number(form.duration_minutes.value),
        max_participants: Number(form.max_participants.value),
        price: Number(form.price.value),
        currency: form.currency.value.trim().toUpperCase(),
        is_active: form.is_active.checked,
        is_featured: form.is_featured.checked,
    };

    try {
        if (form.id.value) {
            await apiPut(`/admin/packages/${form.id.value}`, payload);
        } else {
            await apiPost('/admin/packages', payload);
        }
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
