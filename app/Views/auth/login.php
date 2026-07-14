<?php

use App\Core\View;

$pageTitle = 'Log in — Vipasa Yoga';
$pageCss = 'auth';
?>
<div class="auth-page">
    <div class="auth-split">
        <div class="auth-split__branding">
            <img class="auth-split__branding-photo" src="/assets/img/client/pose-side-stretch.webp" alt="">
            <div class="auth-split__branding-content">
                <div class="splash__wordmark" style="color:inherit">Vipasa Yoga</div>
            </div>
            <p class="auth-split__quote">"Yoga is the journey of the self, through the self, to the self." Sign in and continue where you left off.</p>
        </div>
        <div class="auth-split__form-side">
            <div class="auth-split__form-inner">
                <h1 class="auth-card__title">Welcome back</h1>
                <p class="auth-card__subtitle">Sign in to continue your practice.</p>
                <div class="auth-social">
                    <a href="/auth/google" id="google-login-link" class="btn btn-secondary btn-block btn-lg flex items-center justify-center gap-2">
                        <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true">
                            <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62Z"/>
                            <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18Z"/>
                            <path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.68 9c0-.59.1-1.17.27-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33Z"/>
                            <path fill="#EA4335" d="M9 3.58c1.32 0 2.5.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58Z"/>
                        </svg>
                        Continue with Google
                    </a>
                </div>
                <div class="auth-divider">or</div>
                <form id="login-form" novalidate>
                    <div class="form-group">
                        <input type="email" id="email" name="email" class="form-group__control" placeholder=" " required autocomplete="username">
                        <label class="form-group__label" for="email">Email address</label>
                        <div class="form-group__hint"></div>
                        <div class="form-group__error"></div>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-group__control" placeholder=" " required autocomplete="current-password">
                        <label class="form-group__label" for="password">Password</label>
                        <div class="form-group__hint"></div>
                        <div class="form-group__error"></div>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <label class="flex items-center gap-2" style="margin-bottom:0">
                            <input type="checkbox" id="remember" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="/forgot-password" class="text-sm">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-submit">Log in</button>
                </form>
                <p class="auth-card__footer">Don't have an account? <a href="/signup">Sign up</a></p>
            </div>
        </div>
    </div>
</div>
<script type="module">
import { apiPost } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';
import { showFieldError, clearFieldError } from '/assets/js/modules/validation.js';

const form = document.getElementById('login-form');
const submitBtn = document.getElementById('login-submit');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');

const params = new URLSearchParams(window.location.search);
const googleError = params.get('google_error');
if (googleError === 'not_configured') {
    toast.error('Google sign-in isn\'t configured yet. Please use email/password.');
} else if (googleError === 'failed') {
    toast.error('Google sign-in failed. Please try again.');
}

// Carries a booking/browse page a visitor was bounced from (see
// AuthMiddleware) through to Google sign-in too, so "Continue with Google"
// also lands back where they started.
const redirectTo = params.get('redirect');
if (redirectTo) {
    document.getElementById('google-login-link').href = '/auth/google?redirect=' + encodeURIComponent(redirectTo);
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFieldError(emailInput);
    clearFieldError(passwordInput);
    submitBtn.classList.add('is-loading');
    submitBtn.disabled = true;

    try {
        const result = await apiPost('/api/auth/login', {
            email: emailInput.value.trim(),
            password: passwordInput.value,
            remember: document.getElementById('remember').checked,
            redirect: redirectTo || '',
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
        submitBtn.classList.remove('is-loading');
        submitBtn.disabled = false;
    }
});
</script>
