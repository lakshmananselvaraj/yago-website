<?php

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::SESSION_KEY)) {
            Session::set(self::SESSION_KEY, bin2hex(random_bytes(32)));
        }

        return Session::get(self::SESSION_KEY);
    }

    public static function verify(?string $token): bool
    {
        $stored = Session::get(self::SESSION_KEY);

        return is_string($token) && is_string($stored) && hash_equals($stored, $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }
}
