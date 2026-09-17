<?php

namespace App\Support;

use App\Config\Environment;
use PDO;

class Security {
    private const REMEMBER_COOKIE = 'pokejob_remember';
    private const REMEMBER_DURATION = 2592000;

    public static function bootstrap(): void {
        $isSecure = self::isHttps();

        if (Environment::isProduction() && !$isSecure) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=UTF-8');
            exit('Une connexion HTTPS est requise.');
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.gc_maxlifetime', '3600');

        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        $now = time();
        $lastActivity = (int)($_SESSION['last_activity'] ?? $now);
        // Une heure d’inactivité invalide la session, même si le navigateur reste ouvert
        if ($now - $lastActivity > 3600) {
            $wasAuthenticated = isset($_SESSION['user_id']);
            self::destroySession();
            session_start();
            if ($wasAuthenticated) {
                $_SESSION['session_expired'] = true;
            }
        }
        $_SESSION['last_activity'] = $now;

        // Ces en-têtes réduisent l’exposition au chargement de contenu et aux intégrations non autorisées
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cache-Control: private, no-cache, must-revalidate, max-age=0');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' https://code.jquery.com https://cdn.jsdelivr.net; script-src-attr 'none'; style-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src-attr 'unsafe-inline'; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https://cdn.jsdelivr.net; connect-src 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'");
        if ($isSecure) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function isHttps(): bool {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return false;
    }

    public static function verifyCsrf(): bool {
        $provided = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($provided)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $provided);
    }

    public static function requireCsrf(): void {
        if (!self::verifyCsrf()) {
            http_response_code(403);
            exit('La session a expiré. Veuillez actualiser la page.');
        }
    }

    public static function regenerateAfterLogin(): void {
        // Changer l’identifiant de session empêche la réutilisation d’une session fixée avant la connexion
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
        unset($_SESSION['csrf_token']);
        self::csrfToken();
    }

    public static function rememberLogin(PDO $pdo, int $userId): void {
        self::forgetRememberedLogin($pdo);
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_DURATION;

        // Seule l’empreinte du validateur est stockée en base pour protéger un éventuel vol de données
        $stmt = $pdo->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $selector, hash('sha256', $validator), date('Y-m-d H:i:s', $expires)]);
        self::setRememberCookie($selector . ':' . $validator, $expires);
    }

    public static function resumeRememberedLogin(PDO $pdo): void {
        if (isset($_SESSION['user_id']) || empty($_COOKIE[self::REMEMBER_COOKIE])) {
            return;
        }
        $rememberCookie = $_COOKIE[self::REMEMBER_COOKIE];
        if (!is_string($rememberCookie)) {
            self::forgetRememberedLogin($pdo);
            return;
        }
        $parts = explode(':', $rememberCookie, 2);
        if (count($parts) !== 2 || !ctype_xdigit($parts[0]) || !ctype_xdigit($parts[1])) {
            self::forgetRememberedLogin($pdo);
            return;
        }
        [$selector, $validator] = $parts;
        try {
            $stmt = $pdo->prepare('SELECT rt.id AS token_id, rt.token_hash, rt.expires_at, u.id, u.first_name, u.last_name, u.email, u.email_verified_at, u.session_version FROM remember_tokens rt JOIN users u ON u.id = rt.user_id WHERE rt.selector = ? LIMIT 1');
            $stmt->execute([$selector]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$record || strtotime((string)$record['expires_at']) <= time() || !hash_equals((string)$record['token_hash'], hash('sha256', $validator)) || empty($record['email_verified_at'])) {
                self::forgetRememberedLogin($pdo);
                return;
            }
            self::regenerateAfterLogin();
            $_SESSION['user_id'] = (int)$record['id'];
            $_SESSION['user_name'] = $record['first_name'];
            $_SESSION['first_name'] = $record['first_name'];
            $_SESSION['last_name'] = $record['last_name'];
            $_SESSION['user_email'] = $record['email'];
            $_SESSION['session_version'] = (int)$record['session_version'];
            $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([(int)$record['id']]);
            self::rememberLogin($pdo, (int)$record['id']);
        } catch (\Throwable $exception) {
            error_log($exception->getMessage());
            self::clearRememberCookie();
        }
    }

    public static function hasRememberedLogin(): bool {
        return !empty($_COOKIE[self::REMEMBER_COOKIE]);
    }

    public static function validateAuthenticatedSession(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $stmt = $pdo->prepare('SELECT session_version FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$_SESSION['user_id']]);
        $storedVersion = $stmt->fetchColumn();
        $sessionVersion = $_SESSION['session_version'] ?? null;

        if ($storedVersion !== false && $sessionVersion !== null && hash_equals((string)$storedVersion, (string)$sessionVersion)) {
            return;
        }

        self::logout($pdo);
        session_start();
        $_SESSION['site_notification'] = [
            'type' => 'warning',
            'message' => 'Votre session a été fermée après une modification de sécurité du compte.',
            'persistent' => true
        ];
        header('Location: /login');
        exit;
    }

    public static function forgetRememberedLogin(?PDO $pdo = null): void {
        $cookie = is_string($_COOKIE[self::REMEMBER_COOKIE] ?? null) ? $_COOKIE[self::REMEMBER_COOKIE] : '';
        $selector = explode(':', $cookie, 2)[0] ?? '';
        if ($pdo && $selector !== '' && ctype_xdigit($selector)) {
            $pdo->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$selector]);
        }
        self::clearRememberCookie();
    }

    public static function forgetAllRememberedLogins(PDO $pdo, int $userId): void {
        $pdo->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$userId]);
        self::clearRememberCookie();
    }

    public static function logout(?PDO $pdo = null): void {
        self::forgetRememberedLogin($pdo);
        self::destroySession();
    }

    private static function destroySession(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    private static function setRememberCookie(string $value, int $expires): void {
        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires' => $expires,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        $_COOKIE[self::REMEMBER_COOKIE] = $value;
    }

    private static function clearRememberCookie(): void {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires' => time() - 42000,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[self::REMEMBER_COOKIE]);
    }

    public static function clientKey(string $scope, string $identifier = ''): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return hash('sha256', $scope . '|' . $ip . '|' . mb_strtolower(trim($identifier)));
    }

    public static function identifierKey(string $scope, string $identifier): string {
        return hash('sha256', $scope . '|' . mb_strtolower(trim($identifier)));
    }

    public static function requireMethod(string $method, bool $json = false): void {
        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === strtoupper($method)) {
            return;
        }
        http_response_code(405);
        header('Allow: ' . strtoupper($method));
        if ($json) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}
