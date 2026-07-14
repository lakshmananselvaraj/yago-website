<?php

namespace App\Core;

use App\Models\ClientProfile;
use App\Models\Role;
use App\Models\User;

final class Auth
{
    private const SESSION_KEY = '_auth_user_id';
    private const REMEMBER_COOKIE = 'vipasa_remember';

    private static ?array $user = null;
    private static bool $resolved = false;
    private static ?array $permissions = null;

    public static function attempt(string $email, string $password, bool $remember = false): ?array
    {
        $user = User::findBy('email', $email);

        if (!$user || $user['password_hash'] === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        self::login($user, $remember);

        return $user;
    }

    public static function login(array $user, bool $remember = false): void
    {
        Session::regenerate();
        Session::set(self::SESSION_KEY, $user['id']);
        self::$user = $user;
        self::$resolved = true;

        User::update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        if ($remember) {
            self::setRememberCookie((int) $user['id']);
        }
    }

    public static function logout(): void
    {
        $userId = self::id();
        if ($userId) {
            User::update($userId, ['remember_token' => null]);
        }

        self::$user = null;
        self::$resolved = true;
        self::$permissions = null;
        Session::remove(self::SESSION_KEY);
        Session::destroy();

        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            setcookie(self::REMEMBER_COOKIE, '', time() - 3600, '/');
        }
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user ? (int) $user['id'] : null;
    }

    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }

        self::$resolved = true;

        $userId = Session::get(self::SESSION_KEY);
        if ($userId) {
            self::$user = User::find($userId);
            return self::$user;
        }

        self::$user = self::attemptRememberLogin();

        return self::$user;
    }

    /**
     * Where the current user should land right after login (or when a
     * guest-only page redirects an already-authenticated visitor away).
     * The single source of truth for role-aware redirects — used by the
     * login endpoint, the Google OAuth callback, and GuestMiddleware, so
     * the three never drift out of sync with each other.
     */
    public static function redirectHome(): string
    {
        $user = self::user();
        if ($user === null) {
            return '/login';
        }

        return match ($user['role']) {
            'admin' => '/admin',
            'instructor' => '/trainer/dashboard',
            default => ClientProfile::findByUserId((int) $user['id']) !== null ? '/dashboard' : '/onboarding/profile',
        };
    }

    /**
     * Validates a candidate post-login redirect target (from `?redirect=` on
     * /login, round-tripped through the login form). Only ever accept a
     * same-site relative path — a bare "//evil.com" or "https://evil.com"
     * would otherwise let an attacker craft a login link that silently
     * forwards a victim off-site after they authenticate (open redirect).
     */
    public static function isSafeRedirectPath(?string $path): bool
    {
        return $path !== null
            && $path !== ''
            && $path[0] === '/'
            && !str_starts_with($path, '//')
            && !str_contains($path, '://');
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();

        return $user !== null && in_array($user['role'], $roles, true);
    }

    /**
     * Fine-grained FEATURE permission check (role_permissions), layered on
     * top of the coarse role gate used for dashboard access. Memoized per
     * request, same pattern as user().
     */
    public static function can(string $permission): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        if (self::$permissions === null) {
            self::$permissions = Role::permissionSlugsForRole($user['role']);
        }

        return in_array($permission, self::$permissions, true);
    }

    private static function attemptRememberLogin(): ?array
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if (!$cookie || !str_contains($cookie, '|')) {
            return null;
        }

        [$userId, $token] = explode('|', $cookie, 2);
        $user = User::find((int) $userId);

        if (!$user || empty($user['remember_token']) || !hash_equals($user['remember_token'], hash('sha256', $token))) {
            return null;
        }

        Session::set(self::SESSION_KEY, $user['id']);

        return $user;
    }

    private static function setRememberCookie(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        User::update($userId, ['remember_token' => hash('sha256', $token)]);

        setcookie(self::REMEMBER_COOKIE, $userId . '|' . $token, [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
