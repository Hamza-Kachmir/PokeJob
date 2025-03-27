<?php
namespace App\Controllers\Auth;

require_once __DIR__ . '/../../../app/Config/Database.php';
require_once __DIR__ . '/../../../app/Models/User.php';

// Redirection si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php?route=home');
    exit;
}

$success_message = !empty($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['success_message']);

$error_message = ''; // Initialisation du message d'erreur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC); // Utilisation de \PDO::FETCH_ASSOC

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['photo']      = $user['photo'];

            header('Location: /index.php?route=home');
            exit;
        } else {
            $error_message = 'Mot de passe incorrect.';
        }
    } else {
        $error_message = 'Aucun utilisateur trouvé avec cet email.';
    }
}

require_once __DIR__ . '/../../../app/Views/Auth/login.php';