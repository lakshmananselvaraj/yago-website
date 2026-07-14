<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Setting;

final class SettingController extends Controller
{
    private const MAX_LOGO_BYTES = 2 * 1024 * 1024;
    private const ALLOWED_LOGO_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];

    public function page(Request $request): void
    {
        $settings = [
            'site_name' => Setting::get('site_name', 'Vipasa Yoga'),
            'tax_percent' => Setting::get('tax_percent', 5),
            'default_currency' => Setting::get('default_currency', 'INR'),
            'default_timezone' => Setting::get('default_timezone', 'Asia/Kolkata'),
            'site_logo_path' => Setting::get('site_logo_path', ''),
            'social_facebook' => Setting::get('social_facebook', ''),
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_youtube' => Setting::get('social_youtube', ''),
            'default_language' => Setting::get('default_language', 'en'),
            'google_client_id' => Setting::get('google_client_id', ''),
            'google_client_secret_set' => Setting::get('google_client_secret', '') !== '',
            'google_redirect_uri' => Setting::get('google_redirect_uri', ''),
            'payment_gateway' => Setting::get('payment_gateway', 'razorpay'),
            'razorpay_key_id' => Setting::get('razorpay_key_id', ''),
            'razorpay_key_secret_set' => Setting::get('razorpay_key_secret', '') !== '',
            'stripe_public_key' => Setting::get('stripe_public_key', ''),
            'stripe_secret_key_set' => Setting::get('stripe_secret_key', '') !== '',
            'mail_driver' => Setting::get('mail_driver', 'log'),
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', 587),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password_set' => Setting::get('mail_password', '') !== '',
            'mail_from_address' => Setting::get('mail_from_address', 'no-reply@vipasa.demo'),
            'mail_from_name' => Setting::get('mail_from_name', 'Vipasa Yoga'),
            'session_lifetime_minutes' => Setting::get('session_lifetime_minutes', 120),
        ];

        $this->view('admin/settings', [
            'settings' => $settings,
        ], 'dashboard');
    }

    public function save(Request $request): void
    {
        $validator = $this->validate($request, $this->rules());

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $values = [
            'site_name' => (string) $request->input('site_name'),
            'tax_percent' => (string) $request->input('tax_percent'),
            'default_currency' => strtoupper((string) $request->input('default_currency')),
            'default_timezone' => (string) $request->input('default_timezone'),
        ];

        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }

        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, $values);

        $this->success(null, 'Settings updated.');
    }

    public function saveWebsite(Request $request): void
    {
        $values = [
            'social_facebook' => trim((string) $request->input('social_facebook', '')),
            'social_instagram' => trim((string) $request->input('social_instagram', '')),
            'social_twitter' => trim((string) $request->input('social_twitter', '')),
            'social_youtube' => trim((string) $request->input('social_youtube', '')),
            'default_language' => trim((string) $request->input('default_language', 'en')) ?: 'en',
        ];

        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }

        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, ['section' => 'website']);
        $this->success(null, 'Website settings updated.');
    }

    public function uploadLogo(Request $request): void
    {
        if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a logo file.', 422);
        }

        $file = $_FILES['logo'];
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_LOGO_MIMES[$mime])) {
            $this->fail('Please upload a JPG, PNG, WEBP, or SVG image.', 422);
        }

        if ($file['size'] > self::MAX_LOGO_BYTES) {
            $this->fail('Logo must be smaller than 2MB.', 422);
        }

        $dir = dirname(__DIR__, 3) . '/public/assets/img/site';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'logo-' . time() . '.' . self::ALLOWED_LOGO_MIMES[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded logo. Please try again.', 500);
        }

        $existing = Setting::get('site_logo_path', '');
        if ($existing) {
            $oldPath = dirname(__DIR__, 3) . '/public' . $existing;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $publicPath = '/assets/img/site/' . $filename;
        Setting::set('site_logo_path', $publicPath);
        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, ['section' => 'logo']);

        $this->success(['logo_path' => $publicPath], 'Logo updated.');
    }

    public function saveGoogle(Request $request): void
    {
        Setting::set('google_client_id', trim((string) $request->input('client_id', '')));
        Setting::set('google_redirect_uri', trim((string) $request->input('redirect_uri', '')));

        $secret = trim((string) $request->input('client_secret', ''));
        if ($secret !== '') {
            Setting::set('google_client_secret', $secret);
        }

        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, ['section' => 'google']);
        $this->success(null, 'Google login settings updated.');
    }

    public function savePayments(Request $request): void
    {
        Setting::set('payment_gateway', (string) $request->input('payment_gateway', 'razorpay'));
        Setting::set('razorpay_key_id', trim((string) $request->input('razorpay_key_id', '')));
        Setting::set('stripe_public_key', trim((string) $request->input('stripe_public_key', '')));

        $razorpaySecret = trim((string) $request->input('razorpay_key_secret', ''));
        if ($razorpaySecret !== '') {
            Setting::set('razorpay_key_secret', $razorpaySecret);
        }

        $stripeSecret = trim((string) $request->input('stripe_secret_key', ''));
        if ($stripeSecret !== '') {
            Setting::set('stripe_secret_key', $stripeSecret);
        }

        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, ['section' => 'payments']);
        $this->success(null, 'Payment gateway settings updated.');
    }

    public function saveMail(Request $request): void
    {
        Setting::set('mail_driver', (string) $request->input('mail_driver', 'log'));
        Setting::set('mail_host', trim((string) $request->input('mail_host', '')));
        Setting::set('mail_port', (string) (int) $request->input('mail_port', 587));
        Setting::set('mail_username', trim((string) $request->input('mail_username', '')));

        $password = (string) $request->input('mail_password', '');
        if ($password !== '') {
            Setting::set('mail_password', $password);
        }

        Setting::set('mail_from_address', trim((string) $request->input('mail_from_address', '')));
        Setting::set('mail_from_name', trim((string) $request->input('mail_from_name', '')));

        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, ['section' => 'smtp']);
        $this->success(null, 'SMTP settings updated.');
    }

    public function saveSecurity(Request $request): void
    {
        $minutes = max(5, (int) $request->input('session_lifetime_minutes', 120));
        Setting::set('session_lifetime_minutes', (string) $minutes);

        ActivityLog::log(Auth::id(), 'settings_updated', 'setting', null, ['section' => 'security']);
        $this->success(null, 'Security settings updated.');
    }

    private function rules(): array
    {
        return [
            'site_name' => 'required|max:150',
            'tax_percent' => 'required|numeric|min:0',
            'default_currency' => 'required|max:3',
            'default_timezone' => 'required|max:60',
        ];
    }
}
