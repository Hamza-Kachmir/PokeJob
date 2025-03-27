<?php
class Contact {
    protected $db;

    // Constructeur pour initialiser la connexion à la base de données
    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Ajoute un nouveau contact à la base de données
    public function addContact($user_id, $first_name, $last_name, $email, $subject, $message) {
        try {
            // Préparation de la requête SQL pour insérer un nouveau contact
            $stmt = $this->db->prepare("INSERT INTO contacts (user_id, first_name, last_name, email, subject, message) VALUES (:user_id, :fn, :ln, :em, :sub, :msg)");
            // Exécution de la requête avec les valeurs fournies
            $stmt->execute([
                'user_id' => $user_id,
                'fn'      => $first_name,
                'ln'      => $last_name,
                'em'      => $email,
                'sub'     => $subject,
                'msg'     => $message
            ]);
            return true; // Retourne vrai si l'insertion est réussie
        } catch (PDOException $e) {
            // Lance une exception en cas d'erreur lors de l'insertion
            throw new Exception("Erreur lors de l'insertion du contact : " . $e->getMessage());
        }
    }
}
?>