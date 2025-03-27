<?php
namespace App\Controllers\Auth;

require_once __DIR__ . '/../../../app/Config/Database.php';
require_once __DIR__ . '/../../../app/Models/User.php';

$error_message = ''; // Initialisation du message d'erreur
$success_message = ''; // Initialisation du message de succès

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données POST
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    // Validation des champs obligatoires
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = "Tous les champs sont obligatoires.";
    } else {
        // Utilisation du modèle User pour vérifier l'existence de l'utilisateur
        $userModel = new \User($pdo);
        $user = $userModel->getUserByEmail($email);

        if ($user) {
            // Vérification de la correspondance des noms
            if (strcasecmp($user['first_name'], $first_name) === 0 && strcasecmp($user['last_name'], $last_name) === 0) {
                // Génération d'un token de réinitialisation
                $token = md5($email . time());
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_email'] = $email;
                $success_message = "Un lien de réinitialisation a été envoyé à votre adresse email.";
            } else {
                $error_message = "Les informations ne correspondent pas à nos enregistrements.";
            }
        } else {
            $error_message = "Aucun utilisateur trouvé avec cet email.";
        }
    }
}

require_once __DIR__ . '/../../../app/Views/Auth/forgotPassword.php';
?>