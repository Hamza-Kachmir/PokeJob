<?php

namespace App\Controllers\Auth;

use App\Config\Database;
use App\Models\AuthCode;
use App\Models\User;
use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Support\Security;
use App\Support\Validation;
use PDO;

class RegisterController {
    public function __construct(private ?PDO $pdo = null) {}

    public function checkEmailAvailability(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false], 405);
        }

        Security::requireCsrf();
        $email = Validation::email($_POST['email'] ?? '');
        if ($email === null) {
            $this->json(['success' => true, 'valid' => false, 'available' => false]);
        }

        $pdo = $this->pdo ?? Database::getConnection();
        $limiter = new RateLimiter($pdo);
        if (!$limiter->allow(Security::clientKey('email-availability'), 30, 600)) {
            $this->json(['success' => false, 'error' => 'Trop de vérifications. Réessayez dans quelques minutes.'], 429);
        }

        $available = !(new User($pdo))->getUserByEmail($email);
        $this->json(['success' => true, 'valid' => true, 'available' => $available]);
    }

    public function handleRegister(): void {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $errorMessage = '';
        $firstName = '';
        $lastName = '';
        $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $pdo = $this->pdo ?? Database::getConnection();
            $limiter = new RateLimiter($pdo);
            $key = Security::clientKey('register');

            if (!$limiter->allow($key, 5, 3600)) {
                $errorMessage = 'Trop de tentatives. Réessayez dans quelques minutes.';
            } else {
                $firstName = Validation::text($_POST['first_name'] ?? '', 50, true);
                $lastName = Validation::text($_POST['last_name'] ?? '', 50, true);
                $email = Validation::email($_POST['email'] ?? '');
                $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
                $passwordConfirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';
                $passwordErrors = Validation::passwordErrors($password);

                if ($firstName === null || $lastName === null || $email === null) {
                    $errorMessage = 'Veuillez renseigner des informations valides.';
                } elseif ($password !== $passwordConfirm) {
                    $errorMessage = 'Les mots de passe ne correspondent pas.';
                } elseif ($passwordErrors) {
                    $errorMessage = 'Le mot de passe doit respecter les règles suivantes : ' . implode(', ', $passwordErrors) . '.';
                } else {
                    $userModel = new User($pdo);
                    $existingUser = $userModel->getUserByEmail($email);
                    if ($existingUser) {
                        $errorMessage = 'Impossible de créer le compte avec ces informations.';
                    } elseif (!$limiter->allow(Security::identifierKey('register-email-target', $email), 3, 3600)) {
                        $errorMessage = 'Trop de demandes pour cette adresse. Réessayez plus tard.';
                    } else {
                        try {
                            $code = AuthCode::generate();
                            (new Mailer())->sendCode($email, $firstName, $code, 'VERIFY_EMAIL');
                            $_SESSION['pending_registration'] = [
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $email,
                                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                                'code_hash' => AuthCode::hashCode($code),
                                'code_expires_at' => time() + 600,
                                'attempts' => 0,
                                'flow_id' => bin2hex(random_bytes(16))
                            ];
                            unset($_SESSION['pending_email_change']);
                            header('Location: /verify-email');
                            exit;
                        } catch (\Throwable $exception) {
                            error_log($exception->getMessage());
                            $errorMessage = 'Le compte n’a pas pu être créé. Veuillez réessayer.';
                        }
                    }
                }
            }
        }

        $pageTitle = 'Inscription - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/Auth/register.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }

    private function json(array $payload, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
