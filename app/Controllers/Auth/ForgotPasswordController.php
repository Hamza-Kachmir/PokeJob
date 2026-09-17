<?php

namespace App\Controllers\Auth;

use App\Config\Database;
use App\Models\AuthCode;
use App\Models\User;
use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Support\Security;
use App\Support\Validation;

class ForgotPasswordController {
    public function requestCode(): void {
        $errorMessage = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $responseStartedAt = hrtime(true);
            Security::requireCsrf();
            $email = Validation::email($_POST['email'] ?? '');
            $pdo = Database::getConnection();
            $limiter = new RateLimiter($pdo);

            $allowedForIdentity = $limiter->allow(Security::identifierKey('forgot-password', $email ?? ''), 4, 3600);
            $allowedForIp = $limiter->allow(Security::clientKey('forgot-password-ip'), 20, 3600);
            if (!$allowedForIdentity || !$allowedForIp) {
                $errorMessage = 'Trop de demandes. Réessayez plus tard.';
            } elseif (!$email) {
                $errorMessage = 'Veuillez saisir une adresse e-mail valide.';
            } else {
                $user = (new User($pdo))->getUserByEmail($email);
                $_SESSION['reset_flow_active'] = true;
                $_SESSION['reset_user_id'] = $user ? (int)$user['id'] : -1;

                if ($user) {
                    try {
                        $authCode = new AuthCode($pdo);
                        $pendingCode = $authCode->prepare((int)$user['id'], 'RESET_PASSWORD');
                        (new Mailer())->sendCode($user['email'], $user['first_name'], $pendingCode['code'], 'RESET_PASSWORD');
                        $authCode->activate($pendingCode['id'], (int)$user['id'], 'RESET_PASSWORD');
                    } catch (\Throwable $exception) {
                        error_log($exception->getMessage());
                    }
                }

                self::delayAnonymousResponse($responseStartedAt);
                header('Location: /verify-reset-code');
                exit;
            }
        }

        $pageTitle = 'Mot de passe oublié - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/Auth/forgotPassword.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }

    public function verifyCode(): void {
        if (empty($_SESSION['reset_flow_active'])) {
            header('Location: /forgot-password');
            exit;
        }

        $errorMessage = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $userId = (int)($_SESSION['reset_user_id'] ?? 0);
            $codeInput = is_string($_POST['code'] ?? null) ? $_POST['code'] : '';
            $code = preg_replace('/\D/', '', $codeInput);
            $pdo = Database::getConnection();
            $limiter = new RateLimiter($pdo);

            if (!$limiter->allow(Security::identifierKey('reset-code', (string)$userId), 8, 900)) {
                $errorMessage = 'Trop de tentatives. Recommencez la procédure.';
            } elseif (strlen($code) !== 6 || !(new AuthCode($pdo))->verify($userId, 'RESET_PASSWORD', $code)) {
                $errorMessage = 'Code incorrect ou expiré.';
            } else {
                $_SESSION['reset_authorized_user_id'] = $userId;
                $_SESSION['reset_authorized_until'] = time() + 600;
                unset($_SESSION['reset_flow_active'], $_SESSION['reset_user_id']);
                header('Location: /reset-password');
                exit;
            }
        }

        $pageTitle = 'Vérification du code - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/Auth/verifyResetCode.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }

    public function resetPassword(): void {
        $userId = (int)($_SESSION['reset_authorized_user_id'] ?? 0);
        $validUntil = (int)($_SESSION['reset_authorized_until'] ?? 0);
        if ($userId <= 0 || $validUntil < time()) {
            unset($_SESSION['reset_authorized_user_id'], $_SESSION['reset_authorized_until']);
            header('Location: /forgot-password');
            exit;
        }

        $pdo = Database::getConnection();
        $userModel = new User($pdo);
        $user = $userModel->getUserById($userId);
        if (!$user) {
            unset($_SESSION['reset_authorized_user_id'], $_SESSION['reset_authorized_until']);
            header('Location: /forgot-password');
            exit;
        }

        $errorMessage = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $confirmation = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';
            $passwordErrors = Validation::passwordErrors($password);

            if ($password !== $confirmation) {
                $errorMessage = 'Les mots de passe ne correspondent pas.';
            } elseif ($passwordErrors) {
                $errorMessage = 'Le mot de passe doit respecter les règles suivantes : ' . implode(', ', $passwordErrors) . '.';
            } elseif (password_verify($password, $user['password'])) {
                $errorMessage = 'Le nouveau mot de passe doit être différent du mot de passe actuel.';
            } else {
                if ($userModel->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT)) === false) {
                    $errorMessage = 'Le mot de passe n’a pas pu être modifié.';
                } else {
                    Security::forgetAllRememberedLogins($pdo, $userId);
                    unset($_SESSION['reset_authorized_user_id'], $_SESSION['reset_authorized_until']);
                    Security::regenerateAfterLogin();
                    $_SESSION['success_message'] = 'Votre mot de passe a été modifié. Vous pouvez vous connecter.';
                    header('Location: /login');
                    exit;
                }
            }
        }

        $pageTitle = 'Nouveau mot de passe - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/Auth/resetPassword.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }

    private static function delayAnonymousResponse(int $startedAt): void {
        $minimumDuration = 1200000000 + random_int(0, 200000000);
        $remainingNanoseconds = $minimumDuration - (hrtime(true) - $startedAt);
        if ($remainingNanoseconds > 0) {
            usleep((int)ceil($remainingNanoseconds / 1000));
        }
    }
}
