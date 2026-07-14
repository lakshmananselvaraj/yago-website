<?php

namespace App\Core;

final class RateLimiter
{
    /**
     * Returns true if the action is allowed (and records the hit), false if the limit was exceeded.
     */
    public static function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $db = Database::connection();
        $now = time();

        $stmt = $db->prepare('SELECT * FROM rate_limits WHERE bucket_key = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();

        if (!$row) {
            $insert = $db->prepare('INSERT INTO rate_limits (bucket_key, hits, window_started_at) VALUES (:key, 1, :now)');
            $insert->execute(['key' => $key, 'now' => date('Y-m-d H:i:s', $now)]);
            return true;
        }

        $windowStarted = strtotime($row['window_started_at']);

        if (($now - $windowStarted) > $decaySeconds) {
            $update = $db->prepare('UPDATE rate_limits SET hits = 1, window_started_at = :now WHERE bucket_key = :key');
            $update->execute(['key' => $key, 'now' => date('Y-m-d H:i:s', $now)]);
            return true;
        }

        if ((int) $row['hits'] >= $maxAttempts) {
            return false;
        }

        $update = $db->prepare('UPDATE rate_limits SET hits = hits + 1 WHERE bucket_key = :key');
        $update->execute(['key' => $key]);

        return true;
    }
}
