<?php
namespace App\Controllers\Auth;

// Réinitialisation du tableau de session
$_SESSION = array();

// Vérification si les cookies de session sont utilisés
if (ini_get("session.use_cookies")) {
    // Récupération des paramètres des cookies de session
    $params = session_get_cookie_params();

    // Suppression du cookie de session en définissant une date d'expiration passée
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruction de la session
session_destroy();

// Redirection vers la page d'accueil après la déconnexion
header('Location: /index.php?route=home');
exit;
?>