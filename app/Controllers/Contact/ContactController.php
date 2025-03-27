<?php
namespace App\Controllers\Contact;

require_once __DIR__ . '/../../../app/Config/Database.php';
require_once __DIR__ . '/../../../app/Models/Contact.php';

// Initialisation des modèles et messages
$contactModel    = new \Contact($pdo);
$success_message = '';
$error_message   = '';

// Initialisation des champs de formulaire
$first_name = '';
$last_name  = '';
$email      = '';

// Pré-remplissage des champs si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    $first_name = $_SESSION['first_name'] ?? '';
    $last_name  = $_SESSION['last_name'] ?? '';
    $email      = $_SESSION['email'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données POST
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $subject    = trim($_POST['subject'] ?? '');
    $message    = trim($_POST['message'] ?? '');

    // Validation des champs obligatoires
    if (empty($first_name) || empty($last_name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "Tous les champs sont obligatoires.";
    } else {
        // Récupération de l'ID utilisateur si connecté
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        try {
            // Ajout du message de contact à la base de données
            $contactModel->addContact($user_id, $first_name, $last_name, $email, $subject, $message);
            $success_message = "Merci pour votre message, nous vous répondrons au plus vite.";
        } catch (Exception $e) {
            $error_message = "Une erreur est survenue lors de l'envoi du message.";
        }
    }
}

require_once __DIR__ . '/../../../app/Views/Contact/contact.php';
?>