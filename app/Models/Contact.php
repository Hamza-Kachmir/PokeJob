<?php

namespace App\Models;

use PDO;

class Contact {
    public function __construct(private PDO $pdo) {}

    public function addContact(
        ?int $userId,
        string $firstName,
        string $lastName,
        string $email,
        string $subject,
        string $message
    ): bool {
        $stmt = $this->pdo->prepare("INSERT INTO contacts (user_id, first_name, last_name, email, subject, message) VALUES (:user_id, :fn, :ln, :em, :sub, :msg)");
        return $stmt->execute([
            'user_id' => $userId,
            'fn' => $firstName,
            'ln' => $lastName,
            'em' => $email,
            'sub' => $subject,
            'msg' => $message
        ]);
    }
}
