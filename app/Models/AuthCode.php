<?php

namespace App\Models;

use App\Config\Environment;
use PDO;

class AuthCode {
    public function __construct(private PDO $pdo) {}

    public function prepare(int $userId, string $purpose): array {
        $code = self::generate();
        $hash = self::hashCode($code);
        $expiresAt = date('Y-m-d H:i:s', time() + 600);

        $stmt = $this->pdo->prepare('INSERT INTO auth_codes (user_id, purpose, code_hash, expires_at, used_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $purpose, $hash, $expiresAt]);

        return ['id' => (int)$this->pdo->lastInsertId(), 'code' => $code];
    }

    public function activate(int $id, int $userId, string $purpose): void {

        $ownsTransaction = !$this->pdo->inTransaction();
        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }
        try {
            $this->pdo->prepare('UPDATE auth_codes SET used_at = NOW() WHERE user_id = ? AND purpose = ? AND used_at IS NULL')
                ->execute([$userId, $purpose]);
            $activation = $this->pdo->prepare('UPDATE auth_codes SET used_at = NULL, attempts = 0 WHERE id = ? AND user_id = ? AND purpose = ?');
            $activation->execute([$id, $userId, $purpose]);
            if ($activation->rowCount() !== 1) {
                throw new \RuntimeException('Activation du code impossible.');
            }
            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function verify(int $userId, string $purpose, string $code): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT id, code_hash, expires_at, attempts FROM auth_codes WHERE user_id = ? AND purpose = ? AND used_at IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE');
            $stmt->execute([$userId, $purpose]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record || strtotime($record['expires_at']) < time() || (int)$record['attempts'] >= 5) {
                $this->pdo->commit();
                return false;
            }

            // Chaque essai est comptabilisé sous verrou avant la comparaison
            $this->pdo->prepare('UPDATE auth_codes SET attempts = attempts + 1 WHERE id = ?')->execute([$record['id']]);
            $valid = self::matches($code, $record['code_hash']);
            if ($valid) {
                $this->pdo->prepare('UPDATE auth_codes SET used_at = NOW() WHERE id = ?')->execute([$record['id']]);
            }
            $this->pdo->commit();
            return $valid;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public static function generate(): string {
        return (string)random_int(100000, 999999);
    }

    public static function matches(string $code, string $hash): bool {
        return hash_equals($hash, self::hashCode($code));
    }

    public static function hashCode(string $code): string {
        $pepper = Environment::isProduction()
            ? Environment::requireSecret('AUTH_CODE_PEPPER', ['local-development-only', 'REMPLACER_PAR_UNE_LONGUE_VALEUR_ALEATOIRE_ET_SECRETE'])
            : Environment::get('AUTH_CODE_PEPPER', 'local-development-only');

        // La base conserve une empreinte protégée plutôt que le code envoyé à l’utilisateur
        return hash_hmac('sha256', $code, $pepper);
    }
}
