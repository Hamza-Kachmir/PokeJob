<?php
namespace App\Controllers\Auth;

require_once __DIR__ . '/../../../app/Config/Database.php';
require_once __DIR__ . '/../../../app/Models/User.php';

$error_message = ''; // Initialisation du message d'erreur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données POST
    $first_name       = trim($_POST['first_name'] ?? '');
    $last_name        = trim($_POST['last_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Vérification de la correspondance des mots de passe
    if ($password !== $password_confirm) {
        $error_message = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            // Utilisation du modèle User pour vérifier l'existence de l'email et ajouter un nouvel utilisateur
            $userModel = new \User($pdo);

            if ($userModel->getUserByEmail($email)) {
                $error_message = 'Cet email est déjà utilisé.';
            } else {
                // Hachage du mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Ajout du nouvel utilisateur
                if ($userModel->addUser($first_name, $last_name, $email, $hashedPassword)) {
                    $_SESSION['success_message'] = "Votre compte a été créé avec succès.";
                    header('Location: /index.php?route=login');
                    exit;
                } else {
                    $error_message = 'Erreur lors de la création du compte.';
                }
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../../app/Views/Auth/register.php';
?>