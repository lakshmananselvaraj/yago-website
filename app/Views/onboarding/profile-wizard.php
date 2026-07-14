<?php

use App\Core\View;

$pageTitle = 'Your profile — Vipasa Yoga';
$pageCss = 'onboarding';
$portal = 'client';
$active = 'profile';

$genders = [
    'female' => 'Female',
    'male' => 'Male',
    'non_binary' => 'Non-binary',
    'prefer_not_to_say' => 'Prefer not to say',
];
$timezones = [
    'UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
    'America/Sao_Paulo', 'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Europe/Moscow',
    'Africa/Cairo', 'Africa/Johannesburg', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Bangkok',
    'Asia/Shanghai', 'Asia/Tokyo', 'Asia/Singapore', 'Australia/Sydney', 'Pacific/Auckland',
];
$currentTimezone = $profile['timezone'] ?? null;
$initials = mb_strtoupper(mb_substr($user['name'] ?? 'Y', 0, 1));
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="services-hero" style="margin:0;text-align:left">
            <h1 class="services-hero__title" style="margin:0">Your profile</h1>
            <p class="services-hero__subtitle" style="margin:0">Keep your details up to date.</p>
        </div>
    </div>
    <div>
        <div class="onboarding-panel" style="max-width:560px;margin-inline:auto">
            <div class="flex items-center gap-4 mb-6">
                <div class="avatar avatar-xl" id="avatar-preview" style="background:var(--color-primary-soft);color:var(--color-primary);font-weight:var(--font-weight-semibold)">
                    <?php if (!empty($profile['avatar_path'])): ?>
                    <img src="<?= View::e($profile['avatar_path']) ?>" alt="">
                    <?php else: ?>
                    <span><?= View::e($initials) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" id="avatar-upload-btn">Upload photo</button>
                    <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/webp" hidden>
                    <p class="form-group__hint" style="margin-top:var(--space-2)">JPG, PNG, or WEBP — up to 2MB.</p>
                </div>
            </div>

            <form id="profile-form">
                <div class="form-section__grid">
                    <div class="form-group">
                        <input type="text" id="name" name="name" class="form-group__control" placeholder=" " required minlength="2" maxlength="150" value="<?= View::e($user['name'] ?? '') ?>">
                        <label class="form-group__label" for="name">Full name</label>
                        <div class="form-group__hint"></div>
                        <div class="form-group__error"></div>
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" class="form-group__control" placeholder=" " value="<?= View::e($user['email'] ?? '') ?>" disabled>
                        <label class="form-group__label" for="email">Email address</label>
                        <div class="form-group__hint">Contact support to change your email.</div>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="phone" name="phone" class="form-group__control" placeholder=" " value="<?= View::e($user['phone'] ?? '') ?>">
                        <label class="form-group__label" for="phone">Phone number</label>
                        <div class="form-group__hint"></div>
                        <div class="form-group__error"></div>
                    </div>
                    <div class="form-group">
                        <input type="number" id="age" name="age" class="form-group__control" placeholder=" " min="1" max="120" value="<?= View::e((string) ($profile['age'] ?? '')) ?>">
                        <label class="form-group__label" for="age">Age</label>
                        <div class="form-group__hint"></div>
                        <div class="form-group__error"></div>
                    </div>
                    <div class="form-section__full">
                        <label>Gender</label>
                        <div class="option-grid">
                            <?php foreach ($genders as $value => $label): ?>
                            <label class="option-card">
                                <input type="radio" name="gender" value="<?= View::e($value) ?>" class="sr-only" <?= ($profile['gender'] ?? '') === $value ? 'checked' : '' ?>>
                                <span class="option-card__title"><?= View::e($label) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="country" name="country" class="form-group__control" placeholder=" " maxlength="100" value="<?= View::e($profile['country'] ?? '') ?>">
                        <label class="form-group__label" for="country">Country</label>
                        <div class="form-group__hint"></div>
                        <div class="form-group__error"></div>
                    </div>
                    <div class="form-section__full">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone" required>
                            <?php foreach ($timezones as $tz): ?>
                            <option value="<?= View::e($tz) ?>" <?= $currentTimezone === $tz ? 'selected' : '' ?>><?= View::e($tz) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-section__full">
                        <label for="bio">Short bio</label>
                        <textarea id="bio" name="bio" maxlength="200" placeholder="A sentence or two about you and your practice"><?= View::e($profile['bio'] ?? '') ?></textarea>
                        <div class="form-group__hint"><span id="bio-count">0</span>/200</div>
                    </div>
                    <div class="form-section__full">
                        <label for="medical_notes">Health information (private, shared only with a trainer you book)</label>
                        <textarea id="medical_notes" name="medical_notes" maxlength="2000" placeholder="Injuries, conditions, or anything your instructor should know"><?= View::e($profile['medical_notes'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="onboarding-nav">
                    <span></span>
                    <button type="submit" class="btn btn-primary" id="save-btn">Save profile</button>
                </div>
            </form>
        </div>

        <div class="onboarding-panel" style="max-width:560px;margin-inline:auto;margin-top:var(--space-6)">
            <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Google Account</h2>
            <?php if (!empty($user['google_id'])): ?>
            <p class="text-muted">Your account is linked to Google — you can sign in with either method.</p>
            <?php else: ?>
            <p class="text-muted mb-3">Not linked yet. Connect Google for one-click sign-in.</p>
            <a href="/auth/google" class="btn btn-secondary btn-sm">Connect Google Account</a>
            <?php endif; ?>
        </div>

        <div class="onboarding-panel" style="max-width:560px;margin-inline:auto;margin-top:var(--space-6)">
            <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Password</h2>
            <form id="password-form">
                <div class="form-section__grid">
                    <?php if (!empty($user['password_hash'])): ?>
                    <div class="form-group form-section__full">
                        <input type="password" id="current_password" name="current_password" class="form-group__control" placeholder=" ">
                        <label class="form-group__label" for="current_password">Current password</label>
                        <div class="form-group__error"></div>
                    </div>
                    <?php endif; ?>
                    <div class="form-group form-section__full">
                        <input type="password" id="new_password" name="new_password" class="form-group__control" placeholder=" " minlength="8">
                        <label class="form-group__label" for="new_password"><?= empty($user['password_hash']) ? 'Set a password' : 'New password' ?></label>
                        <div class="form-group__hint">At least 8 characters.</div>
                        <div class="form-group__error"></div>
                    </div>
                </div>
                <div class="form-group__error" id="password-form-error"></div>
                <div class="onboarding-nav">
                    <span></span>
                    <button type="submit" class="btn btn-primary" id="password-save-btn">Update password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="module">
import { apiPut, apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';
import { showFieldError, clearFieldError } from '/assets/js/modules/validation.js';

document.querySelectorAll('.option-card').forEach((card) => {
    const input = card.querySelector('input');
    const sync = () => {
        document.querySelectorAll(`input[name="${input.name}"]`).forEach((sibling) => {
            sibling.closest('.option-card').classList.toggle('is-selected', sibling.checked);
        });
    };
    input.addEventListener('change', sync);
    sync();
});

const timezoneSelect = document.getElementById('timezone');
if (!<?= json_encode($currentTimezone !== null) ?>) {
    try {
        const detected = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if ([...timezoneSelect.options].some((opt) => opt.value === detected)) {
            timezoneSelect.value = detected;
        }
    } catch {
        // ignore — fall back to the default selected option
    }
}

const bioInput = document.getElementById('bio');
const bioCount = document.getElementById('bio-count');
const updateBioCount = () => { bioCount.textContent = String(bioInput.value.length); };
bioInput.addEventListener('input', updateBioCount);
updateBioCount();

const avatarBtn = document.getElementById('avatar-upload-btn');
const avatarInput = document.getElementById('avatar-input');
const avatarPreview = document.getElementById('avatar-preview');

avatarBtn.addEventListener('click', () => avatarInput.click());
avatarInput.addEventListener('change', async () => {
    const file = avatarInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch('/api/profile/avatar', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-CSRF-Token': csrfToken },
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Upload failed.');
        }
        avatarPreview.innerHTML = `<img src="${result.data.avatar_path}" alt="">`;
        toast.success('Photo updated.');
    } catch (err) {
        toast.error(err.message);
    }
});

const form = document.getElementById('profile-form');
const saveBtn = document.getElementById('save-btn');
const nameInput = document.getElementById('name');
const phoneInput = document.getElementById('phone');

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    [nameInput, phoneInput].forEach(clearFieldError);
    saveBtn.classList.add('is-loading');
    saveBtn.disabled = true;

    const selectedGender = form.querySelector('input[name="gender"]:checked');

    try {
        const result = await apiPut('/api/profile', {
            name: nameInput.value.trim(),
            phone: phoneInput.value.trim(),
            age: document.getElementById('age').value,
            gender: selectedGender ? selectedGender.value : '',
            country: document.getElementById('country').value.trim(),
            timezone: timezoneSelect.value,
            bio: bioInput.value.trim(),
            medical_notes: document.getElementById('medical_notes').value.trim(),
            complete: true,
        });
        window.location.href = result.data.redirect;
    } catch (err) {
        if (err.errors) {
            Object.entries(err.errors).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) showFieldError(input, messages[0]);
            });
        }
        toast.error(err.message);
        saveBtn.classList.remove('is-loading');
        saveBtn.disabled = false;
    }
});

const passwordForm = document.getElementById('password-form');
const passwordSaveBtn = document.getElementById('password-save-btn');
const passwordErrorBox = document.getElementById('password-form-error');

passwordForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    passwordErrorBox.textContent = '';
    passwordSaveBtn.classList.add('is-loading');
    passwordSaveBtn.disabled = true;

    try {
        await apiPost('/api/profile/change-password', {
            current_password: document.getElementById('current_password')?.value ?? '',
            new_password: document.getElementById('new_password').value,
        });
        toast.success('Password updated.');
        passwordForm.reset();
    } catch (err) {
        passwordErrorBox.textContent = err.message;
        toast.error(err.message);
    } finally {
        passwordSaveBtn.classList.remove('is-loading');
        passwordSaveBtn.disabled = false;
    }
});
</script>
