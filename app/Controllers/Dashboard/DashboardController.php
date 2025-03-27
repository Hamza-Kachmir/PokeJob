<?php
namespace App\Controllers\Dashboard;

require_once __DIR__ . '/../../../app/Models/CompanyCounter.php';
require_once __DIR__ . '/../../../app/Models/User.php';

class DashboardController
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Méthode pour afficher le dashboard admin
    public function index()
    {
        // Récupération du nombre total d'utilisateurs
        $userModel = new \User($this->pdo);
        $countUsers = $userModel->count();

        // Récupération du compteur d'entreprises cumulatif
        $companyCounter = new \CompanyCounter($this->pdo);
        $countCompanies = $companyCounter->getCount();

        // Récupération de la liste des utilisateurs
        $stmt = $this->pdo->query("SELECT first_name, last_name, email, id FROM users ORDER BY first_name ASC");
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Récupération du nombre de messages non lus
        $stmt2 = $this->pdo->query("SELECT COUNT(*) FROM contacts WHERE status = 'Non lu'");
        $countUnreadMessages = $stmt2->fetchColumn();

        // Pour afficher la photo et le nom de l'utilisateur admin connecté
        $admin_photo = $_SESSION['photo'] ?? '/assets/images/default-profile.png';
        $admin_name  = $_SESSION['first_name'] ?? 'Admin';

        require __DIR__ . '/../../Views/Dashboard/dashboard.php';
    }

    // Méthode pour supprimer un utilisateur
    public function deleteUser()
    {
        $userId = $_GET['id'] ?? 0;
        if ($userId) {
            $userModel = new \User($this->pdo);
            $userModel->delete((int)$userId);
        }
        header('Location: /index.php?route=dashboard');
        exit;
    }    

    // Méthode pour réinitialiser le compteur d'entreprises
    public function resetCompanies()
    {
        $companyCounter = new \CompanyCounter($this->pdo);
        $companyCounter->reset();
        header('Location: /index.php?route=dashboard');
        exit;
    }

    // Méthode pour renvoyer des statistiques (non utilisées)
    public function statsJson()
    {
        $data = [
            'labels' => [],
            'users'  => [],
            'companies' => [],
        ];
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}