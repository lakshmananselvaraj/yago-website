<?php

use App\Core\View;

$pageTitle = 'Settings — Vipasa Yoga Admin';
$pageCss = 'services';
$portal = 'admin';
$active = 'settings';
?>
<div class="container" style="padding-block:var(--space-8) var(--space-16)">
    <div class="services-hero" style="text-align:left;margin:0 0 var(--space-6)">
        <h1 class="services-hero__title" style="margin:0">Settings</h1>
        <p class="services-hero__subtitle" style="margin:0">Platform-wide configuration — payment/mail/Google credentials here take priority over .env.</p>
    </div>

    <div class="card mb-8" style="padding:var(--space-6);max-width:640px;margin-inline:auto">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">General</h2>
        <form id="settings-form">
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <input type="text" name="site_name" class="form-group__control" placeholder=" " required maxlength="150" value="<?= View::e((string) $settings['site_name']) ?>">
                    <label class="form-group__label">Site name</label>
                </div>
                <div class="form-group">
                    <input type="number" name="tax_percent" class="form-group__control" placeholder=" " required min="0" step="0.01" value="<?= View::e((string) $settings['tax_percent']) ?>">
                    <label class="form-group__label">Tax percent</label>
                </div>
                <div class="form-group">
                    <input type="text" name="default_currency" class="form-group__control" placeholder=" " required maxlength="3" style="text-transform:uppercase" value="<?= View::e((string) $settings['default_currency']) ?>">
                    <label class="form-group__label">Default currency</label>
                </div>
                <div class="form-group">
                    <input type="text" name="default_timezone" class="form-group__control" placeholder=" " required maxlength="60" value="<?= View::e((string) $settings['default_timezone']) ?>">
                    <label class="form-group__label">Default timezone</label>
                </div>
                <div class="form-group__error" id="settings-form-error"></div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Save General</button>
            </div>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6);max-width:640px;margin-inline:auto">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Website &amp; Theme</h2>
        <div class="flex items-center gap-4 mb-4">
            <?php if ($settings['site_logo_path']): ?>
            <img src="<?= View::e($settings['site_logo_path']) ?>" alt="" style="height:48px">
            <?php else: ?>
            <span class="text-muted">No logo uploaded yet.</span>
            <?php endif; ?>
            <label class="btn btn-secondary btn-sm" style="cursor:pointer">
                Upload Logo
                <input type="file" id="logo-input" accept="image/jpeg,image/png,image/webp,image/svg+xml" style="display:none">
            </label>
        </div>
        <form id="website-form">
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <input type="text" name="social_facebook" class="form-group__control" placeholder=" " value="<?= View::e($settings['social_facebook']) ?>">
                    <label class="form-group__label">Facebook URL</label>
                </div>
                <div class="form-group">
                    <input type="text" name="social_instagram" class="form-group__control" placeholder=" " value="<?= View::e($settings['social_instagram']) ?>">
                    <label class="form-group__label">Instagram URL</label>
                </div>
                <div class="form-group">
                    <input type="text" name="social_twitter" class="form-group__control" placeholder=" " value="<?= View::e($settings['social_twitter']) ?>">
                    <label class="form-group__label">X / Twitter URL</label>
                </div>
                <div class="form-group">
                    <input type="text" name="social_youtube" class="form-group__control" placeholder=" " value="<?= View::e($settings['social_youtube']) ?>">
                    <label class="form-group__label">YouTube URL</label>
                </div>
                <div class="form-group">
                    <select name="default_language" class="form-group__control">
                        <option value="en" <?= $settings['default_language'] === 'en' ? 'selected' : '' ?>>English</option>
                        <option value="hi" <?= $settings['default_language'] === 'hi' ? 'selected' : '' ?>>Hindi</option>
                    </select>
                    <label class="form-group__label">Default language</label>
                    <div class="form-group__hint">Stored for future use — full site translation is not yet built.</div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Save Website Settings</button>
            </div>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6);max-width:640px;margin-inline:auto">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Google Login</h2>
        <form id="google-form">
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <input type="text" name="client_id" class="form-group__control" placeholder=" " value="<?= View::e($settings['google_client_id']) ?>">
                    <label class="form-group__label">Client ID</label>
                </div>
                <div class="form-group">
                    <input type="password" name="client_secret" class="form-group__control" placeholder="<?= $settings['google_client_secret_set'] ? '••••••••  (leave blank to keep)' : ' ' ?>">
                    <label class="form-group__label">Client secret</label>
                </div>
                <div class="form-group">
                    <input type="text" name="redirect_uri" class="form-group__control" placeholder=" " value="<?= View::e($settings['google_redirect_uri']) ?>">
                    <label class="form-group__label">Redirect URI</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Save Google Settings</button>
            </div>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6);max-width:640px;margin-inline:auto">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Payment Gateway</h2>
        <form id="payments-form">
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <select name="payment_gateway" class="form-group__control">
                        <option value="razorpay" <?= $settings['payment_gateway'] === 'razorpay' ? 'selected' : '' ?>>Razorpay</option>
                        <option value="stripe" <?= $settings['payment_gateway'] === 'stripe' ? 'selected' : '' ?>>Stripe</option>
                    </select>
                    <label class="form-group__label">Active gateway</label>
                </div>
                <div class="form-group">
                    <input type="text" name="razorpay_key_id" class="form-group__control" placeholder=" " value="<?= View::e($settings['razorpay_key_id']) ?>">
                    <label class="form-group__label">Razorpay key ID</label>
                </div>
                <div class="form-group">
                    <input type="password" name="razorpay_key_secret" class="form-group__control" placeholder="<?= $settings['razorpay_key_secret_set'] ? '••••••••  (leave blank to keep)' : ' ' ?>">
                    <label class="form-group__label">Razorpay key secret</label>
                </div>
                <div class="form-group">
                    <input type="text" name="stripe_public_key" class="form-group__control" placeholder=" " value="<?= View::e($settings['stripe_public_key']) ?>">
                    <label class="form-group__label">Stripe public key</label>
                </div>
                <div class="form-group">
                    <input type="password" name="stripe_secret_key" class="form-group__control" placeholder="<?= $settings['stripe_secret_key_set'] ? '••••••••  (leave blank to keep)' : ' ' ?>">
                    <label class="form-group__label">Stripe secret key</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Save Payment Settings</button>
            </div>
        </form>
    </div>

    <div class="card mb-8" style="padding:var(--space-6);max-width:640px;margin-inline:auto">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">SMTP / Email</h2>
        <form id="mail-form">
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <select name="mail_driver" class="form-group__control">
                        <option value="log" <?= $settings['mail_driver'] === 'log' ? 'selected' : '' ?>>Log to file (dev)</option>
                        <option value="smtp" <?= $settings['mail_driver'] === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                    </select>
                    <label class="form-group__label">Mail driver</label>
                </div>
                <div class="form-group">
                    <input type="text" name="mail_host" class="form-group__control" placeholder=" " value="<?= View::e($settings['mail_host']) ?>">
                    <label class="form-group__label">SMTP host</label>
                </div>
                <div class="form-group">
                    <input type="number" name="mail_port" class="form-group__control" placeholder=" " value="<?= View::e((string) $settings['mail_port']) ?>">
                    <label class="form-group__label">SMTP port</label>
                </div>
                <div class="form-group">
                    <input type="text" name="mail_username" class="form-group__control" placeholder=" " value="<?= View::e($settings['mail_username']) ?>">
                    <label class="form-group__label">SMTP username</label>
                </div>
                <div class="form-group">
                    <input type="password" name="mail_password" class="form-group__control" placeholder="<?= $settings['mail_password_set'] ? '••••••••  (leave blank to keep)' : ' ' ?>">
                    <label class="form-group__label">SMTP password</label>
                </div>
                <div class="form-group">
                    <input type="text" name="mail_from_address" class="form-group__control" placeholder=" " value="<?= View::e($settings['mail_from_address']) ?>">
                    <label class="form-group__label">From address</label>
                </div>
                <div class="form-group">
                    <input type="text" name="mail_from_name" class="form-group__control" placeholder=" " value="<?= View::e($settings['mail_from_name']) ?>">
                    <label class="form-group__label">From name</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Save SMTP Settings</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding:var(--space-6);max-width:640px;margin-inline:auto">
        <h2 style="font-size:var(--font-size-lg);margin-bottom:var(--space-4)">Security</h2>
        <form id="security-form">
            <div class="form-group">
                <input type="number" name="session_lifetime_minutes" class="form-group__control" placeholder=" " min="5" value="<?= View::e((string) $settings['session_lifetime_minutes']) ?>">
                <label class="form-group__label">Session timeout (minutes)</label>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="submit" class="btn btn-primary">Save Security Settings</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
import { apiPut } from '/assets/js/modules/api.js';
import toast from '/assets/js/modules/toast.js';

const form = document.getElementById('settings-form');
const errorBox = document.getElementById('settings-form-error');

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorBox.textContent = '';

    const payload = {
        site_name: form.site_name.value.trim(),
        tax_percent: Number(form.tax_percent.value),
        default_currency: form.default_currency.value.trim().toUpperCase(),
        default_timezone: form.default_timezone.value.trim(),
    };

    try {
        await apiPut('/admin/settings', payload);
        toast.success('Settings updated.');
    } catch (err) {
        errorBox.textContent = err.message;
        toast.error(err.message);
    }
});

document.getElementById('website-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const f = event.target;
    try {
        await apiPut('/admin/settings/website', {
            social_facebook: f.social_facebook.value.trim(),
            social_instagram: f.social_instagram.value.trim(),
            social_twitter: f.social_twitter.value.trim(),
            social_youtube: f.social_youtube.value.trim(),
            default_language: f.default_language.value,
        });
        toast.success('Website settings updated.');
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('logo-input').addEventListener('change', async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('logo', file);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const response = await fetch('/admin/settings/logo', {
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

document.getElementById('google-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const f = event.target;
    try {
        await apiPut('/admin/settings/google', {
            client_id: f.client_id.value.trim(),
            client_secret: f.client_secret.value,
            redirect_uri: f.redirect_uri.value.trim(),
        });
        toast.success('Google settings updated.');
        f.client_secret.value = '';
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('payments-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const f = event.target;
    try {
        await apiPut('/admin/settings/payments', {
            payment_gateway: f.payment_gateway.value,
            razorpay_key_id: f.razorpay_key_id.value.trim(),
            razorpay_key_secret: f.razorpay_key_secret.value,
            stripe_public_key: f.stripe_public_key.value.trim(),
            stripe_secret_key: f.stripe_secret_key.value,
        });
        toast.success('Payment settings updated.');
        f.razorpay_key_secret.value = '';
        f.stripe_secret_key.value = '';
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('mail-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const f = event.target;
    try {
        await apiPut('/admin/settings/mail', {
            mail_driver: f.mail_driver.value,
            mail_host: f.mail_host.value.trim(),
            mail_port: Number(f.mail_port.value),
            mail_username: f.mail_username.value.trim(),
            mail_password: f.mail_password.value,
            mail_from_address: f.mail_from_address.value.trim(),
            mail_from_name: f.mail_from_name.value.trim(),
        });
        toast.success('SMTP settings updated.');
        f.mail_password.value = '';
    } catch (err) {
        toast.error(err.message);
    }
});

document.getElementById('security-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const f = event.target;
    try {
        await apiPut('/admin/settings/security', {
            session_lifetime_minutes: Number(f.session_lifetime_minutes.value),
        });
        toast.success('Security settings updated.');
    } catch (err) {
        toast.error(err.message);
    }
});
</script>
