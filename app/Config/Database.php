<?php
$host       = getenv('DB_HOST') ?: 'localhost';
$port       = getenv('DB_PORT') ?: '3306';
$dbname     = getenv('DB_NAME') ?: 'pokejob';
$dbuser     = getenv('DB_USER') ?: 'root';
$dbpassword = getenv('DB_PASSWORD') ?: '';

try {
    // Construction de la chaîne de connexion DSN
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $dbuser, $dbpassword);
    // Configuration des attributs PDO pour une meilleure gestion des erreurs et des résultats
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // En cas d'erreur, on logge l'erreur et on affiche un message générique à l'utilisateur
    error_log("Erreur de connexion : " . $e->getMessage());
    echo "Une erreur est survenue. Veuillez contacter l'administrateur.";
    exit;
}
?>