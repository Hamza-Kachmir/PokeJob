<?php

namespace App\Support;

final class Asset {
    public static function url(string $path): string {
        if (!str_starts_with($path, '/')) {
            throw new \InvalidArgumentException('Le chemin de la ressource doit commencer par /.');
        }

        $documentRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
        $filePath = $documentRoot . str_replace('/', DIRECTORY_SEPARATOR, $path);
        $version = is_file($filePath) ? filemtime($filePath) : false;

        return $version === false ? $path : $path . '?v=' . $version;
    }
}
