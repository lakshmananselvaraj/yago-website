<?php

use App\Core\View;

$pageTitle = 'Manage Instructors — Vipasa Yoga';
$pageCss = 'services';
$portal = 'admin';
$active = 'instructors';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">Manage Instructors</h1>
            <p class="services-hero__subtitle" style="margin:0">Adding an instructor creates their account and emails them a link to set their password.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="add-instructor-btn">+ Add Instructor</button>
    </div>

    <?php if (empty($instructors)): ?>
    <p class="text-muted text-center">No instructors yet.</p>
    <?php else: ?>
    <div class="flex flex-col gap-4" style="max-width:820px;margin-inline:auto">
        <?php foreach ($instructors as $instructor): ?>
        <div class="card" style="padding:var(--space-5)" data-instructor='<?= View::e(json_encode($instructor)) ?>'>
            <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                <div style="font-weight:var(--font-weight-semibold)">
                    <?= View::e($instructor['name']) ?>
                    <?php if ($instructor['status'] === 'active'): ?><span class="badge badge-confirmed">Active</span>
                    <?php elseif ($instructor['status'] === 'test'): ?><span class="badge badge-pending">Test</span>
                    <?php else: ?><span class="badge badge-cancelled">Inactive</span><?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-ghost btn-sm edit-instructor-btn">Edit</button>
                    <button type="button" class="btn btn-ghost btn-sm toggle-active-btn" data-id="<?= (int) $instructor['id'] ?>">
                        <?= $instructor['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                    </button>
                </div>
            </div>
            <p class="text-muted" style="font-size:var(--font-size-sm);margin:0">
                <?= View::e($instructor['email'] ?? '') ?>
                <?php if (!empty($instructor['headline'])): ?> · <?= View::e($instructor['headline']) ?><?php endif; ?>
                · <?= (int) $instructor['experience_years'] ?> yr(s) experience
            </p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="instructor-modal-overlay" style="display:none">
    <div class="modal modal-glass">
        <div class="modal__header">
            <div class="modal__title" id="instructor-modal-title">Add Instructor</div>
            <button type="button" class="modal__close" id="instructor-modal-close" aria-label="Close">&times;</button>
        </div>
        <form id="instructor-form">
            <input type="hidden" name="id" value="">
            <div class="modal__body flex flex-col gap-4">
                <div class="form-group">
                    <input type="text" name="name" class="form-group__control" placeholder=" " required maxlength="150">
                    <label class="form-group__label">Name</label>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-group__control" placeholder=" " required maxlength="190">
                    <label class="form-group__label">Email</label>
                </div>
                <p class="text-muted" id="instructor-email-hint" style="font-size:var(--font-size-sm);margin:-0.5rem 0 0;display:none">Email can't be changed after the instructor is created.</p>
                <div class="form-group">
                    <input type="text" name="phone" class="form-group__control" placeholder=" " maxlength="20">
                    <label class="form-group__label">Phone (optional)</label>
                </div>
                <div class="form-group">
                    <input type="text" name="headline" class="form-group__control" placeholder=" " maxlength="200">
                    <label class="form-group__label">Headline (optional)</label>
                </div>
                <div class="form-group">
                    <textarea name="bio" class="form-group__control" placeholder=" " rows="3"></textarea>
                    <label class="form-group__label">Bio (optional)</label>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <div class="form-group" style="flex:1;min-width:120px">
                        <input type="number" name="experience_years" class="form-group__control" placeholder=" " min="0" value="0">
                        <label class="form-group__label">Experience (years)</label>
                    </div>
                    <div class="form-group" style="flex:1;min-width:160px">
                        <input type="text" name="timezone" class="form-group__control" placeholder=" " required value="UTC">
                        <label class="form-group__label">Timezone</label>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" name="specialties" class="form-group__control" placeholder=" ">
                    <label class="form-group__label">Specialties (comma-separated)</label>
                </div>
                <div class="form-group">
                    <input type="text" name="certificates" class="form-group__control" placeholder=" ">
                    <label class="form-group__label">Certificates (comma-separated)</label>
                </div>
                <div class="form-group__error" id="instructor-form-error"></div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn-ghost" id="instructor-modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="instructor-submit-btn">Save Instructor</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
import { apiPost, apiPut } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

const overlay = document.getElementById('instructor-modal-overlay');
const form = document.getElementById('instructor-form');
const title = document.getElementById('instructor-modal-title');
const submitBtn = document.getElementById('instructor-submit-btn');
const errorBox = document.getElementById('instructor-form-error');
const emailHint = document.getElementById('instructor-email-hint');

function openModal(instructor) {
    form.reset();
    errorBox.textContent = '';
    if (instructor) {
        title.textContent = 'Edit Instructor';
        form.id.value = instructor.id;
        form.name.value = instructor.name;
        form.email.value = instructor.email || '';
        form.email.disabled = true;
        emailHint.style.display = 'block';
        form.phone.value = instructor.phone || '';
        form.headline.value = instructor.headline || '';
        form.bio.value = instructor.bio || '';
        form.experience_years.value = instructor.experience_years;
        form.timezone.value = instructor.timezone || 'UTC';
        form.specialties.value = (instructor.specialties || []).join(', ');
        form.certificates.value = (instructor.certificates || []).join(', ');
    } else {
        title.textContent = 'Add Instructor';
        form.id.value = '';
        form.email.disabled = false;
        emailHint.style.display = 'none';
        form.timezone.value = 'UTC';
    }
    overlay.style.display = 'flex';
}

function closeModal() {
    overlay.style.display = 'none';
}

document.getElementById('add-instructor-btn').addEventListener('click', () => openModal(null));
document.getElementById('instructor-modal-close').addEventListener('click', closeModal);
document.getElementById('instructor-modal-cancel').addEventListener('click', closeModal);
overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

document.querySelectorAll('.edit-instructor-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const instructor = JSON.parse(btn.closest('[data-instructor]').dataset.instructor);
        openModal(instructor);
    });
});

document.querySelectorAll('.toggle-active-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        btn.disabled = true;
        try {
            await apiPost(`/admin/instructors/${btn.dataset.id}/toggle-active`, {});
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
        name: form.name.value.trim(),
        email: form.email.value.trim(),
        phone: form.phone.value.trim(),
        headline: form.headline.value.trim(),
        bio: form.bio.value.trim(),
        experience_years: Number(form.experience_years.value || 0),
        timezone: form.timezone.value.trim(),
        specialties: form.specialties.value.trim(),
        certificates: form.certificates.value.trim(),
    };

    try {
        if (form.id.value) {
            await apiPut(`/admin/instructors/${form.id.value}`, payload);
        } else {
            await apiPost('/admin/instructors', payload);
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
