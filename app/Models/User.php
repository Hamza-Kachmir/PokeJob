<?php

namespace App\Models;

use PDO;

class User {
    public function __construct(private PDO $pdo) {}

    public function addUser(string $firstName, string $lastName, string $email, string $hashedPassword): bool {
        $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
    }

    public function markEmailVerified(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateLastLogin(int $id): void {
        $this->pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$id]);
    }

    public function updatePassword(int $id, string $hashedPassword): int|false {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?, session_version = session_version + 1 WHERE id = ?");
        $stmt->execute([$hashedPassword, $id]);
        if ($stmt->rowCount() !== 1) {
            return false;
        }

        $versionStmt = $this->pdo->prepare("SELECT session_version FROM users WHERE id = ?");
        $versionStmt->execute([$id]);
        $version = $versionStmt->fetchColumn();
        return $version === false ? false : (int)$version;
    }

    public function deleteAccount(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getUserByEmail(string $email): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExistsForAnotherUser(string $email, int $excludeId): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([$email, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function updateEmail(int $id, string $email): int|false {
        $stmt = $this->pdo->prepare("UPDATE users SET email = ?, session_version = session_version + 1 WHERE id = ?");
        $stmt->execute([$email, $id]);
        if ($stmt->rowCount() !== 1) {
            return false;
        }

        $versionStmt = $this->pdo->prepare("SELECT session_version FROM users WHERE id = ?");
        $versionStmt->execute([$id]);
        $version = $versionStmt->fetchColumn();
        return $version === false ? false : (int)$version;
    }
}
