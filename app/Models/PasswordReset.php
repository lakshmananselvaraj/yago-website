<?php

namespace App\Models;

use App\Core\Model;

final class PasswordReset extends Model
{
    protected static string $table = 'password_resets';

    public static function createToken(int $userId, int $ttlMinutes = 30): string
    {
        $token = bin2hex(random_bytes(32));

        self::insert([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlMinutes * 60),
        ]);

        return $token;
    }

    /**
     * Verifies a raw token from a clicked email link without consuming it, so the
     * reset-password page can validate the link before the user submits a new
     * password. Returns the user id on success, null if invalid/expired/used.
     */
    public static function verifyByToken(string $token): ?int
    {
        $db = static::db();
        $stmt = $db->prepare(
            'SELECT * FROM password_resets WHERE token_hash = :hash AND used_at IS NULL ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
        $row = $stmt->fetch();

        if (!$row || strtotime($row['expires_at']) < time()) {
            return null;
        }

        return (int) $row['user_id'];
    }

    /**
     * Atomically marks a token used. Call only after the new password has been
     * validated, immediately before persisting it, so a token can't be replayed.
     */
    public static function consume(string $token): bool
    {
        $db = static::db();
        $stmt = $db->prepare(
            'UPDATE password_resets SET used_at = NOW() WHERE token_hash = :hash AND used_at IS NULL AND expires_at >= NOW()'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);

        return $stmt->rowCount() > 0;
    }
}
