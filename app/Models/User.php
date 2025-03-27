<?php
class User {
    protected $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function addUser($first_name, $last_name, $email, $hashedPassword) {
        $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$first_name, $last_name, $email, $hashedPassword]);
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC); // Préfixé avec \PDO::FETCH_ASSOC
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC); // Préfixé
    }

    public function emailExistsForAnotherUser($email, $excludeId) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([$email, $excludeId]);
        return (bool)$stmt->fetch();
    }

    public function updateEmailAndPhoto($id, $email, $photo) {
        $stmt = $this->db->prepare("UPDATE users SET email = ?, photo = ? WHERE id = ?");
        return $stmt->execute([$email, $photo, $id]);
    }

    public function updateEmailPasswordAndPhoto($id, $email, $hashedPassword, $photo) {
        $stmt = $this->db->prepare("UPDATE users SET email = ?, password = ?, photo = ? WHERE id = ?");
        return $stmt->execute([$email, $hashedPassword, $photo, $id]);
    }

    public function all(): array {
        $stmt = $this->db->query("SELECT id, first_name AS name, email, role FROM users");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function stats(string $period): array {
        switch($period) {
            case 'month':
                $sql = "SELECT DATE(created_at) AS label, COUNT(*) AS count 
                        FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                        GROUP BY DATE(created_at)";
                break;
            case 'year':
                $sql = "SELECT DATE_FORMAT(created_at,'%b') AS label, COUNT(*) AS count 
                        FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                        GROUP BY MONTH(created_at)";
                break;
            default:
                $sql = "SELECT DATE(created_at) AS label, COUNT(*) AS count 
                        FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
                        GROUP BY DATE(created_at)";
        }
        $stmt = $this->db->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'labels' => array_column($data, 'label'),
            'counts' => array_column($data, 'count')
        ];
    }
}