<?php

namespace App\Core;

final class GoogleOAuth
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const STATE_SESSION_KEY = 'google_oauth_state';

    public static function isConfigured(): bool
    {
        $config = self::config();

        return $config['client_id'] !== '' && $config['client_secret'] !== '' && $config['redirect_uri'] !== '';
    }

    public static function authorizationUrl(): string
    {
        $config = self::config();
        $state = bin2hex(random_bytes(16));
        Session::set(self::STATE_SESSION_KEY, $state);

        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    public static function verifyState(?string $state): bool
    {
        $stored = Session::get(self::STATE_SESSION_KEY);
        Session::remove(self::STATE_SESSION_KEY);

        return $state !== null && $stored !== null && hash_equals((string) $stored, $state);
    }

    /**
     * Exchanges an authorization code for the signed-in user's Google identity.
     * Returns null on any failure (network error, bad code, missing fields) —
     * callers should treat that as "Google sign-in failed, please try again."
     *
     * @return array{google_id: string, email: string, name: string}|null
     */
    public static function fetchUser(string $code): ?array
    {
        $config = self::config();

        $tokenResponse = self::post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri' => $config['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);

        if (!$tokenResponse || empty($tokenResponse['access_token'])) {
            return null;
        }

        $userInfo = self::get(self::USERINFO_URL, (string) $tokenResponse['access_token']);

        if (!$userInfo || empty($userInfo['sub']) || empty($userInfo['email'])) {
            return null;
        }

        return [
            'google_id' => (string) $userInfo['sub'],
            'email' => (string) $userInfo['email'],
            'name' => (string) ($userInfo['name'] ?? $userInfo['email']),
        ];
    }

    private static function config(): array
    {
        return (require dirname(__DIR__) . '/Config/app.php')['google_oauth'];
    }

    private static function post(string $url, array $fields): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = curl_exec($ch);
        $ok = curl_errno($ch) === 0;
        curl_close($ch);

        if (!$ok || $body === false) {
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }

    private static function get(string $url, string $bearerToken): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $bearerToken],
        ]);
        $body = curl_exec($ch);
        $ok = curl_errno($ch) === 0;
        curl_close($ch);

        if (!$ok || $body === false) {
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }
}
