<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        // Une seule connexion PDO est partagée pendant toute la requête HTTP
        if (self::$instance === null) {
            $host = Environment::get('DB_HOST', 'db');
            $port = Environment::get('DB_PORT', '3306');
            $dbName = Environment::get('DB_NAME', 'pokejob');
            $username = Environment::get('DB_USER', 'root');
            $password = Environment::get('DB_PASSWORD', 'rootpassword');

            if (Environment::isProduction()) {
                $password = Environment::requireSecret('DB_PASSWORD', ['rootpassword']);
                if (strtolower($username) === 'root') {
                    throw new \RuntimeException('DB_USER doit utiliser un compte dédié en production.');
                }
                Environment::requireSecret('AUTH_CODE_PEPPER', ['local-development-only', 'REMPLACER_PAR_UNE_LONGUE_VALEUR_ALEATOIRE_ET_SECRETE']);
            }

            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$instance->exec("SET time_zone = '+00:00'");
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw new PDOException('Connexion à la base de données impossible.');
            }
        }

        return self::$instance;
    }
}
