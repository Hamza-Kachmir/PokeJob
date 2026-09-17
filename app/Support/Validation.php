<?php

namespace App\Support;

class Validation {
    public static function email(mixed $email): ?string {
        if (!is_string($email)) {
            return null;
        }
        $email = mb_strtolower(trim($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 100 ? $email : null;
    }

    public static function passwordErrors(string $password): array {
        $errors = [];
        if (mb_strlen($password) < 8) {
            $errors[] = '8 caractères minimum';
        }
        if (!preg_match('/[A-Z]/u', $password)) {
            $errors[] = 'une majuscule';
        }
        if (!preg_match('/\d/u', $password)) {
            $errors[] = 'un chiffre';
        }
        if (!preg_match('/[^\p{L}\p{N}\s]/u', $password)) {
            $errors[] = 'un caractère spécial';
        }
        if (strlen($password) > 72) {
            $errors[] = 'un mot de passe moins long';
        }
        if (str_contains($password, "\0")) {
            $errors[] = 'aucun caractère nul';
        }
        return $errors;
    }

    public static function text(mixed $value, int $maxLength, bool $required = false): ?string {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($required && $value === '') {
            return null;
        }
        if (mb_strlen($value) > $maxLength) {
            return null;
        }
        return $value;
    }

    public static function date(mixed $value): ?string {
        if (!is_string($value)) {
            return null;
        }
        if ($value === '') {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    public static function url(mixed $value): ?string {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (mb_strlen($value) > 2048 || !filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }
        $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true) ? $value : null;
    }
}
