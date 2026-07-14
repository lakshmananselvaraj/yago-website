<?php

use App\Core\Env;
use App\Models\Setting;

return [
    'name' => Env::get('APP_NAME', 'Vipasa Yoga'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => filter_var(Env::get('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) Env::get('APP_URL', 'http://localhost:8000'), '/'),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Kolkata'),
    'key' => Env::get('APP_KEY', ''),

    'session' => [
        'name' => Env::get('SESSION_NAME', 'vipasa_session'),
        'lifetime' => (int) Setting::get('session_lifetime_minutes', Env::get('SESSION_LIFETIME', 120)),
        'secure' => filter_var(Env::get('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOLEAN),
    ],

    // Mail/payments/Google below resolve DB-stored Settings first (editable from
    // /admin/settings), falling back to .env when no admin override is saved —
    // lets an admin rotate keys without shell/file access, without losing the
    // .env-only deployment path this project started with.
    'mail' => [
        'driver' => Setting::get('mail_driver', Env::get('MAIL_DRIVER', 'log')),
        'from_address' => Setting::get('mail_from_address', Env::get('MAIL_FROM_ADDRESS', 'no-reply@vipasa.demo')),
        'from_name' => Setting::get('mail_from_name', Env::get('MAIL_FROM_NAME', 'Vipasa Yoga')),
        'host' => Setting::get('mail_host', Env::get('MAIL_HOST', '')),
        'port' => (int) Setting::get('mail_port', Env::get('MAIL_PORT', 587)),
        'username' => Setting::get('mail_username', Env::get('MAIL_USERNAME', '')),
        'password' => Setting::get('mail_password', Env::get('MAIL_PASSWORD', '')),
    ],

    'sms' => [
        'driver' => Env::get('SMS_DRIVER', 'log'),
    ],

    'payments' => [
        // 'test' | 'production' — purely informational (drives the "Test Mode"
        // badge on checkout); which keys are live is determined by which
        // values you put in RAZORPAY_*/STRIPE_* below, not by this flag.
        'mode' => Setting::get('payment_mode', Env::get('PAYMENT_MODE', 'test')),
        // 'razorpay' | 'stripe' — the active gateway. Switching gateways, or
        // switching test -> live credentials for the active gateway, is
        // an admin-settings or .env change; no code changes needed.
        'gateway' => Setting::get('payment_gateway', Env::get('PAYMENT_GATEWAY', 'razorpay')),
        'razorpay' => [
            'key_id' => Setting::get('razorpay_key_id', Env::get('RAZORPAY_KEY_ID', '')),
            'key_secret' => Setting::get('razorpay_key_secret', Env::get('RAZORPAY_KEY_SECRET', '')),
        ],
        'stripe' => [
            'public_key' => Setting::get('stripe_public_key', Env::get('STRIPE_PUBLIC_KEY', '')),
            'secret_key' => Setting::get('stripe_secret_key', Env::get('STRIPE_SECRET_KEY', '')),
            'webhook_secret' => Setting::get('stripe_webhook_secret', Env::get('STRIPE_WEBHOOK_SECRET', '')),
        ],
    ],

    'google_oauth' => [
        'client_id' => Setting::get('google_client_id', Env::get('GOOGLE_CLIENT_ID', '')),
        'client_secret' => Setting::get('google_client_secret', Env::get('GOOGLE_CLIENT_SECRET', '')),
        'redirect_uri' => Setting::get('google_redirect_uri', Env::get('GOOGLE_REDIRECT_URI', '')),
    ],

    'rate_limits' => [
        'login' => [
            'max' => (int) Env::get('RATE_LIMIT_LOGIN_MAX', 5),
            'decay' => (int) Env::get('RATE_LIMIT_LOGIN_DECAY', 60),
        ],
        'password_reset' => [
            'max' => (int) Env::get('RATE_LIMIT_PASSWORD_RESET_MAX', 5),
            'decay' => (int) Env::get('RATE_LIMIT_PASSWORD_RESET_DECAY', 60),
        ],
        'contact' => [
            'max' => 5,
            'decay' => 300,
        ],
    ],
];
