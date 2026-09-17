<?php

namespace App\Support;

use PDO;

final class Maintenance {
    private const TASK_NAME = 'routine_cleanup';

    public static function runDailyCleanup(PDO $pdo): void {
        if (random_int(1, 100) !== 1) {
            return;
        }

        try {
            $seed = $pdo->prepare("INSERT IGNORE INTO maintenance_tasks (task_name, last_run_at) VALUES (?, '1970-01-01 00:00:00')");
            $seed->execute([self::TASK_NAME]);

            $stmt = $pdo->prepare('SELECT last_run_at FROM maintenance_tasks WHERE task_name = ? LIMIT 1');
            $stmt->execute([self::TASK_NAME]);
            $lastRunAt = $stmt->fetchColumn();

            if ($lastRunAt !== false && strtotime((string)$lastRunAt) > time() - 86400) {
                return;
            }

            $pdo->beginTransaction();
            $lock = $pdo->prepare('SELECT last_run_at FROM maintenance_tasks WHERE task_name = ? FOR UPDATE');
            $lock->execute([self::TASK_NAME]);
            $lockedLastRunAt = $lock->fetchColumn();
            if ($lockedLastRunAt !== false && strtotime((string)$lockedLastRunAt) > time() - 86400) {
                $pdo->commit();
                return;
            }

            $pdo->exec('DELETE FROM auth_codes WHERE expires_at <= NOW()');
            $pdo->exec('DELETE FROM rate_limits WHERE updated_at < DATE_SUB(NOW(), INTERVAL 2 DAY)');
            $pdo->exec('DELETE FROM remember_tokens WHERE expires_at <= NOW()');
            $pdo->exec('DELETE FROM contacts WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)');
            $update = $pdo->prepare('UPDATE maintenance_tasks SET last_run_at = NOW() WHERE task_name = ?');
            $update->execute([self::TASK_NAME]);
            $pdo->commit();
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Une maintenance en échec ne doit jamais empêcher un visiteur d'utiliser le site
            error_log('Nettoyage automatique : ' . $exception->getMessage());
        }
    }
}
