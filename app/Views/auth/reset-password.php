<?php

use App\Core\View;

$pageTitle = 'Reset password — Vipasa Yoga';
$pageCss = 'auth';
?>
<div class="auth-page">
    <div class="auth-card">
        <?php if (!($valid ?? false)): ?>
            <h1 class="auth-card__title">Reset link invalid</h1>
            <p class="auth-card__subtitle">This password reset link is invalid or has expired. Please request a new one.</p>
            <a href="/forgot-password" class="btn btn-primary btn-block btn-lg">Request a new link</a>
        <?php else: ?>
            <h1 class="auth-card__title">Set a new password</h1>
            <p class="auth-card__subtitle">Choose a strong password for your account.</p>
            <form id="reset-form" novalidate>
                <input type="hidden" id="token" name="token" value="<?= View::e($token ?? '') ?>">
                <div class="form-group">
                    <input type="password" id="password" name="password" class="form-group__control" placeholder=" " required minlength="8" autocomplete="new-password">
                    <label class="form-group__label" for="password">New password</label>
                    <div class="form-group__hint">At least 8 characters.</div>
                    <div class="form-group__error"></div>
                </div>
                <div class="form-group">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-group__control" placeholder=" " required minlength="8" autocomplete="new-password">
                    <label class="form-group__label" for="password_confirmation">Confirm new password</label>
                    <div class="form-group__hint"></div>
                    <div class="form-group__error"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" id="reset-submit">Update password</button>
            </form>
        <?php endif; ?>
        <p class="auth-card__footer"><a href="/login">Back to login</a></p>
    </div>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';
import { showFieldError, clearFieldError } from '/assets/js/modules/validation.js';

const form = document.getElementById('reset-form');

if (form) {
const submitBtn = document.getElementById('reset-submit');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('password_confirmation');
const tokenInput = document.getElementById('token');

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFieldError(passwordInput);
    clearFieldError(confirmInput);

    if (passwordInput.value !== confirmInput.value) {
        showFieldError(confirmInput, 'Passwords do not match.');
        return;
    }

    submitBtn.classList.add('is-loading');
    submitBtn.disabled = true;

    try {
        const result = await apiPost('/api/auth/reset-password', {
            token: tokenInput.value,
            password: passwordInput.value,
            password_confirmation: confirmInput.value,
        });
        window.location.href = result.data.redirect;
    } catch (err) {
        if (err.status === 419) {
            toast.error(err.message);
            setTimeout(() => { window.location.href = '/forgot-password'; }, 1800);
            return;
        }
        if (err.errors) {
            Object.entries(err.errors).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) showFieldError(input, messages[0]);
            });
        }
        toast.error(err.message);
        submitBtn.classList.remove('is-loading');
        submitBtn.disabled = false;
    }
});
}
</script>
