<?php

namespace App\Models;

use App\Core\Model;

final class EmailVerification extends Model
{
    protected static string $table = 'email_verifications';

    public static function createToken(int $userId, int $ttlHours = 24): string
    {
        $token = bin2hex(random_bytes(32));

        self::insert([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlHours * 3600),
        ]);

        return $token;
    }

    /**
     * Verifies a raw token from a clicked email link — deliberately does not require
     * a matching session, since the click may happen on a different device/browser
     * than the one that signed up. Returns the user id on success, null otherwise.
     */
    public static function verifyByToken(string $token): ?int
    {
        $db = static::db();
        $stmt = $db->prepare(
            'SELECT * FROM email_verifications WHERE token_hash = :hash AND verified_at IS NULL ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['hash' => hash('sha256', $token)]);
        $row = $stmt->fetch();

        if (!$row || strtotime($row['expires_at']) < time()) {
            return null;
        }

        $update = $db->prepare('UPDATE email_verifications SET verified_at = NOW() WHERE id = :id');
        $update->execute(['id' => $row['id']]);

        return (int) $row['user_id'];
    }
}
