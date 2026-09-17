<?php

namespace App\Config;

class Environment {
    public static function load(string $rootPath): void {
        $path = $rootPath . '/.env';
        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                throw new \RuntimeException('Lecture du fichier .env impossible.');
            }
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
                $key = trim($key);
                $value = trim($value);
                if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key)) {
                    throw new \RuntimeException("Nom de variable d'environnement invalide : {$key}");
                }
                if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
                    $value = substr($value, 1, -1);
                }
                if (getenv($key) !== false) {
                    continue;
                }
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }

        $environment = strtolower(trim(self::get('APP_ENV', 'development')));
        if (!in_array($environment, ['development', 'test', 'production'], true)) {
            throw new \RuntimeException('APP_ENV doit valoir development, test ou production.');
        }
        date_default_timezone_set('UTC');
    }

    public static function get(string $key, string $default = ''): string {
        $value = $_ENV[$key] ?? getenv($key);
        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function isProduction(): bool {
        return strtolower(trim(self::get('APP_ENV', 'development'))) === 'production';
    }

    public static function requireSecret(string $key, array $forbiddenValues = []): string {
        $value = self::get($key);
        if ($value === '' || in_array($value, $forbiddenValues, true)) {
            throw new \RuntimeException("La variable d'environnement {$key} doit être configurée avec une valeur sûre.");
        }
        return $value;
    }
}
