<?php
class CompanyCounter {
    protected $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    public function getCount(): int {
        $stmt = $this->db->query("SELECT count FROM company_counter LIMIT 1");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC); // Préfixé
        return $result ? (int)$result['count'] : 0;
    }

    public function increment(): bool {
        $stmt = $this->db->query("SELECT count FROM company_counter LIMIT 1");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC); // Préfixé
        if ($result) {
            $stmt = $this->db->prepare("UPDATE company_counter SET count = count + 1");
            return $stmt->execute();
        } else {
            $stmt = $this->db->prepare("INSERT INTO company_counter (count) VALUES (1)");
            return $stmt->execute();
        }
    }

    public function reset(): bool {
        $stmt = $this->db->prepare("UPDATE company_counter SET count = 0");
        return $stmt->execute();
    }
}