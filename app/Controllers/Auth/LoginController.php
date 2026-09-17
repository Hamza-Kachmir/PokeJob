<?php

namespace App\Controllers\Auth;

use App\Config\Database;
use App\Models\User;
use App\Services\RateLimiter;
use App\Support\Security;
use App\Support\Validation;

class LoginController {
    public function handleLogin(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        $errorMessage = '';
        $successMessage = $_SESSION['success_message'] ?? '';
        $email = '';
        $remember = false;
        unset($_SESSION['success_message']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $email = Validation::email($_POST['email'] ?? '');
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $remember = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';
            $pdo = Database::getConnection();
            $limiter = new RateLimiter($pdo);
            $key = Security::identifierKey('login', $email ?? '');
            $ipKey = Security::clientKey('login-ip');

            $allowedForIdentity = $limiter->allow($key, 8, 900);
            $allowedForIp = $limiter->allow($ipKey, 40, 900);
            if (!$allowedForIdentity || !$allowedForIp) {
                $errorMessage = 'Trop de tentatives. Réessayez dans 15 minutes.';
            } elseif (!$email || $password === '') {
                $errorMessage = 'Email ou mot de passe incorrect.';
            } else {
                $userModel = new User($pdo);
                $user = $userModel->getUserByEmail($email);
                $fallbackHash = '$2y$10$EJyqTZ5mv5SoWnA1PB115OvBSNupV/.bFgibJhCI1JUrY.F6SYtmq';
                $candidateHash = $user && !empty($user['email_verified_at']) ? (string)$user['password'] : $fallbackHash;
                $validPassword = password_verify($password, $candidateHash);
                if ($user && !empty($user['email_verified_at']) && $validPassword) {
                    Security::regenerateAfterLogin();
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user_name'] = $user['first_name'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['session_version'] = (int)$user['session_version'];
                    unset($_SESSION['pending_registration'], $_SESSION['pending_email_change']);
                    if ($remember) {
                        Security::rememberLogin($pdo, (int)$user['id']);
                    } else {
                        Security::forgetRememberedLogin($pdo);
                    }
                    $userModel->updateLastLogin((int)$user['id']);
                    $limiter->clear($key);
                    $limiter->clear($ipKey);
                    header('Location: /dashboard');
                    exit;
                }
                $errorMessage = 'Email ou mot de passe incorrect.';
            }
        }

        $pageTitle = 'Connexion - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/Auth/login.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }
}
