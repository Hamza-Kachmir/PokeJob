<?php

namespace App\Services;

use PDO;

class RateLimiter {
    public function __construct(private PDO $pdo) {}

    public function allow(string $key, int $limit, int $windowSeconds): bool {
        // L'upsert est atomique, y compris quand deux requêtes créent la même limite simultanément
        $now = date('Y-m-d H:i:s');
        $threshold = date('Y-m-d H:i:s', time() - $windowSeconds);
        $stmt = $this->pdo->prepare(
            'INSERT INTO rate_limits (limiter_key, attempts, window_started_at) VALUES (?, 1, ?) '
            . 'ON DUPLICATE KEY UPDATE '
            . 'attempts = IF(window_started_at <= ?, 1, attempts + 1), '
            . 'window_started_at = IF(window_started_at <= ?, VALUES(window_started_at), window_started_at)'
        );
        $stmt->execute([$key, $now, $threshold, $threshold]);

        $stmt = $this->pdo->prepare('SELECT attempts FROM rate_limits WHERE limiter_key = ?');
        $stmt->execute([$key]);
        return (int)$stmt->fetchColumn() <= $limit;
    }

    public function clear(string $key): void {
        $stmt = $this->pdo->prepare('DELETE FROM rate_limits WHERE limiter_key = ?');
        $stmt->execute([$key]);
    }
}
