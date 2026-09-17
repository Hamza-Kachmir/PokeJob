<?php

namespace App\Controllers\Auth;

use App\Config\Database;
use App\Models\AuthCode;
use App\Models\User;
use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Support\Security;
use App\Support\Validation;

class VerifyEmailController {
    public function handle(): void {
        $pdo = Database::getConnection();
        $userModel = new User($pdo);
        $pendingRegistration = $_SESSION['pending_registration'] ?? null;
        $pendingEmailChange = $_SESSION['pending_email_change'] ?? null;
        $context = '';
        $user = null;

        if (is_array($pendingRegistration)) {
            $context = 'registration';
            $email = (string)$pendingRegistration['email'];
            $firstName = (string)$pendingRegistration['first_name'];
            $identifier = $email;
        } elseif (is_array($pendingEmailChange) && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)($pendingEmailChange['user_id'] ?? 0)) {
            $context = 'email_change';
            $user = $userModel->getUserById((int)$_SESSION['user_id']);
            if (!$user) {
                header('Location: /login');
                exit;
            }
            $email = (string)$pendingEmailChange['email'];
            $firstName = (string)$user['first_name'];
            $identifier = (string)$user['id'];
        } else {
            header('Location: /login');
            exit;
        }

        $errorMessage = '';
        $successMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $action = is_string($_POST['verification_action'] ?? null) ? $_POST['verification_action'] : 'verify';
            $limiter = new RateLimiter($pdo);

            if ($action === 'change_email' && $context === 'registration') {
                $newEmail = Validation::email($_POST['new_email'] ?? '');
                if ($newEmail === null || $userModel->getUserByEmail($newEmail)) {
                    $errorMessage = 'Adresse e-mail invalide ou déjà utilisée.';
                } elseif ($newEmail === mb_strtolower(trim($email))) {
                    $errorMessage = 'Cette adresse e-mail est déjà utilisée pour votre inscription. Saisissez une autre adresse ou demandez un nouveau code.';
                } else {
                    $flowId = (string)($_SESSION['pending_registration']['flow_id'] ?? session_id());
                    $allowedForFlow = $limiter->allow(Security::identifierKey('verify-change-email-flow', $flowId), 5, 3600);
                    $allowedForIp = $limiter->allow(Security::clientKey('verify-change-email-ip'), 12, 3600);
                    if (!$allowedForFlow || !$allowedForIp || !$limiter->allow(Security::identifierKey('verify-change-email-target', $newEmail), 3, 3600)) {
                        $errorMessage = 'Trop de demandes. Réessayez plus tard.';
                    } else {
                        try {
                            $code = AuthCode::generate();
                            (new Mailer())->sendCode($newEmail, $firstName, $code, 'VERIFY_EMAIL');
                            $_SESSION['pending_registration']['email'] = $newEmail;
                            $_SESSION['pending_registration']['code_hash'] = AuthCode::hashCode($code);
                            $_SESSION['pending_registration']['code_expires_at'] = time() + 600;
                            $_SESSION['pending_registration']['attempts'] = 0;
                            $email = $newEmail;
                            $identifier = $newEmail;
                            $successMessage = 'L’adresse e-mail a été corrigée et un nouveau code a été envoyé.';
                        } catch (\Throwable $exception) {
                            error_log($exception->getMessage());
                            $errorMessage = 'Le nouveau code n’a pas pu être envoyé.';
                        }
                    }
                }
            } elseif ($action === 'resend') {
                $allowedForIdentity = $limiter->allow(Security::identifierKey('verify-resend', $identifier), 3, 3600);
                $allowedForIp = $limiter->allow(Security::clientKey('verify-resend-ip'), 12, 3600);
                if (!$allowedForIdentity || !$allowedForIp) {
                    $errorMessage = 'Trop de demandes. Réessayez plus tard.';
                } else {
                    try {
                        if ($context === 'registration') {
                            $code = AuthCode::generate();
                        } else {
                            $authCode = new AuthCode($pdo);
                            $pendingCode = $authCode->prepare((int)$user['id'], 'VERIFY_EMAIL');
                            $code = $pendingCode['code'];
                        }
                        (new Mailer())->sendCode($email, $firstName, $code, 'VERIFY_EMAIL');
                        if ($context === 'registration') {
                            $_SESSION['pending_registration']['code_hash'] = AuthCode::hashCode($code);
                            $_SESSION['pending_registration']['code_expires_at'] = time() + 600;
                            $_SESSION['pending_registration']['attempts'] = 0;
                        } else {
                            $authCode->activate($pendingCode['id'], (int)$user['id'], 'VERIFY_EMAIL');
                        }
                        $successMessage = 'Un nouveau code a été envoyé.';
                    } catch (\Throwable $exception) {
                        error_log($exception->getMessage());
                        $errorMessage = 'Le code n’a pas pu être envoyé.';
                    }
                }
            } else {
                $codeInput = is_string($_POST['code'] ?? null) ? $_POST['code'] : '';
                $code = preg_replace('/\D/', '', $codeInput);
                $allowedForIdentity = $limiter->allow(Security::identifierKey('verify-code', $identifier), 8, 900);
                $allowedForIp = $limiter->allow(Security::clientKey('verify-code-ip'), 30, 900);
                if (!$allowedForIdentity || !$allowedForIp) {
                    $errorMessage = 'Trop de tentatives. Demandez un nouveau code.';
                } elseif ($context === 'registration') {
                    $pendingRegistration = $_SESSION['pending_registration'];
                    $_SESSION['pending_registration']['attempts'] = (int)$pendingRegistration['attempts'] + 1;
                    $validCode = strlen($code) === 6
                        && (int)$pendingRegistration['attempts'] < 5
                        && (int)$pendingRegistration['code_expires_at'] >= time()
                        && AuthCode::matches($code, (string)$pendingRegistration['code_hash']);
                    if (!$validCode) {
                        $errorMessage = 'Code incorrect ou expiré.';
                    } elseif ($userModel->getUserByEmail((string)$pendingRegistration['email'])) {
                        $errorMessage = 'Cette adresse e-mail est désormais utilisée. Modifiez-la pour terminer l’inscription.';
                    } else {
                        try {
                            $pdo->beginTransaction();
                            $userModel->addUser(
                                $pendingRegistration['first_name'],
                                $pendingRegistration['last_name'],
                                $pendingRegistration['email'],
                                $pendingRegistration['password_hash']
                            );
                            $createdUser = $userModel->getUserByEmail($pendingRegistration['email']);
                            if (!$createdUser) {
                                throw new \RuntimeException('Compte introuvable après création.');
                            }
                            $userModel->markEmailVerified((int)$createdUser['id']);
                            $pdo->commit();
                            unset($_SESSION['pending_registration']);
                            $_SESSION['success_message'] = 'Votre adresse e-mail est confirmée. Vous pouvez vous connecter.';
                            header('Location: /login');
                            exit;
                        } catch (\Throwable $exception) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            error_log($exception->getMessage());
                            $errorMessage = 'Le compte n’a pas pu être créé. Veuillez réessayer.';
                        }
                    }
                } elseif (strlen($code) !== 6 || !(new AuthCode($pdo))->verify((int)$user['id'], 'VERIFY_EMAIL', $code)) {
                    $errorMessage = 'Code incorrect ou expiré.';
                } elseif ($context === 'email_change') {
                    if ($userModel->emailExistsForAnotherUser($email, (int)$user['id'])) {
                        $errorMessage = 'Cette adresse e-mail est désormais utilisée par un autre compte.';
                    } else {
                        try {
                            $newSessionVersion = $userModel->updateEmail((int)$user['id'], $email);
                            if ($newSessionVersion === false) {
                                throw new \RuntimeException('Mise à jour de l’adresse e-mail impossible.');
                            }
                            Security::forgetAllRememberedLogins($pdo, (int)$user['id']);
                            Security::regenerateAfterLogin();
                            $_SESSION['user_email'] = $email;
                            $_SESSION['session_version'] = $newSessionVersion;
                            unset($_SESSION['pending_email_change']);
                            $_SESSION['success_message'] = 'Votre nouvelle adresse e-mail est confirmée.';
                            header('Location: /account');
                            exit;
                        } catch (\Throwable $exception) {
                            error_log($exception->getMessage());
                            $errorMessage = 'Cette adresse e-mail ne peut plus être utilisée.';
                        }
                    }
                }
            }
        }

        $verificationTitle = $context === 'email_change' ? 'Confirmez votre nouvelle adresse e-mail' : 'Vérifiez votre e-mail';
        $canChangeEmail = $context === 'registration';
        $pageTitle = 'Vérification de l’e-mail - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/Auth/verifyEmail.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }
}
