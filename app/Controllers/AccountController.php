<?php

namespace App\Controllers;

use App\Models\AuthCode;
use App\Models\Job;
use App\Models\User;
use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Support\Security;
use App\Support\Validation;
use PDO;

class AccountController {
    private User $userModel;

    public function __construct(private PDO $pdo) {
        $this->userModel = new User($pdo);
    }

    public function index(): void {
        $pageTitle = 'Mon compte - PokéJob';
        $userId = $this->requireUser();
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            Security::logout($this->pdo);
            header('Location: /login');
            exit;
        }

        $feedback = is_array($_SESSION['account_feedback'] ?? null) ? $_SESSION['account_feedback'] : [];
        $successMessage = (string)($feedback['success'] ?? $_SESSION['success_message'] ?? '');
        $errorMessage = (string)($feedback['error'] ?? '');
        $emailModalError = (string)($feedback['email_error'] ?? '');
        $deleteModalError = (string)($feedback['delete_error'] ?? '');
        unset($_SESSION['account_feedback'], $_SESSION['success_message']);
        $firstName = $user['first_name'] ?? '';
        $lastName = $user['last_name'] ?? '';
        $email = $user['email'] ?? '';
        $emailModalOpen = $emailModalError !== '';
        $deleteModalOpen = $deleteModalError !== '';
        $newEmailValue = (string)($feedback['new_email'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $accountAction = is_string($_POST['account_action'] ?? null) ? $_POST['account_action'] : '';
            $sensitiveActions = ['email', 'password', 'delete'];
            $limiter = new RateLimiter($this->pdo);
            $actionKey = Security::identifierKey('account-' . $accountAction, (string)$userId);
            $ipKey = Security::clientKey('account-sensitive-ip');
            $sensitiveActionAllowed = !in_array($accountAction, $sensitiveActions, true)
                || ($limiter->allow($actionKey, 8, 900) && $limiter->allow($ipKey, 30, 900));

            if ($accountAction === 'email') {
                $newEmailValue = is_string($_POST['new_email'] ?? null) ? trim($_POST['new_email']) : '';
                $newEmail = Validation::email($newEmailValue);
                $currentPassword = is_string($_POST['current_password_email'] ?? null) ? $_POST['current_password_email'] : '';
                if (!$sensitiveActionAllowed) {
                    $emailModalError = 'Trop de tentatives. Réessayez dans 15 minutes.';
                    $emailModalOpen = true;
                } elseif (!$newEmail || $this->userModel->emailExistsForAnotherUser($newEmail, $userId)) {
                    $emailModalError = 'Adresse e-mail invalide ou déjà utilisée.';
                    $emailModalOpen = true;
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $emailModalError = 'Le mot de passe est incorrect.';
                    $emailModalOpen = true;
                } elseif ($newEmail === $user['email']) {
                    $successMessage = 'Votre adresse e-mail est déjà à jour.';
                    $email = $user['email'];
                } elseif (!$limiter->allow(Security::identifierKey('account-email-target', $newEmail), 3, 3600)) {
                    $emailModalError = 'Trop de demandes pour cette adresse. Réessayez plus tard.';
                    $emailModalOpen = true;
                } else {
                    try {
                        $authCode = new AuthCode($this->pdo);
                        $pendingCode = $authCode->prepare($userId, 'VERIFY_EMAIL');
                        (new Mailer())->sendCode($newEmail, $firstName, $pendingCode['code'], 'VERIFY_EMAIL');
                        $authCode->activate($pendingCode['id'], $userId, 'VERIFY_EMAIL');
                        $_SESSION['pending_email_change'] = ['user_id' => $userId, 'email' => $newEmail];
                        unset($_SESSION['pending_registration']);
                        header('Location: /verify-email');
                        exit;
                    } catch (\Throwable $exception) {
                        error_log($exception->getMessage());
                        $emailModalError = 'L’adresse e-mail n’a pas pu être modifiée.';
                        $emailModalOpen = true;
                    }
                }
            } elseif ($accountAction === 'password') {
                $currentPassword = is_string($_POST['currentPassword'] ?? null) ? $_POST['currentPassword'] : '';
                $newPassword = is_string($_POST['newPassword'] ?? null) ? $_POST['newPassword'] : '';
                $confirmation = is_string($_POST['confirmNewPassword'] ?? null) ? $_POST['confirmNewPassword'] : '';
                $passwordErrors = Validation::passwordErrors($newPassword);

                if (!$sensitiveActionAllowed) {
                    $errorMessage = 'Trop de tentatives. Réessayez dans 15 minutes.';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $errorMessage = 'Le mot de passe actuel est incorrect.';
                } elseif ($newPassword !== $confirmation) {
                    $errorMessage = 'Les nouveaux mots de passe ne correspondent pas.';
                } elseif ($passwordErrors) {
                    $errorMessage = 'Le mot de passe doit respecter les règles suivantes : ' . implode(', ', $passwordErrors) . '.';
                } elseif (password_verify($newPassword, $user['password'])) {
                    $errorMessage = 'Le nouveau mot de passe doit être différent du mot de passe actuel.';
                } else {
                    $newSessionVersion = $this->userModel->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
                    if ($newSessionVersion === false) {
                        $errorMessage = 'Le mot de passe n’a pas pu être modifié.';
                    } else {
                        Security::forgetAllRememberedLogins($this->pdo, $userId);
                        Security::regenerateAfterLogin();
                        $_SESSION['session_version'] = $newSessionVersion;
                        $limiter->clear($actionKey);
                        $limiter->clear($ipKey);
                        $successMessage = 'Votre mot de passe a été modifié. Les autres appareils ont été déconnectés.';
                    }
                }
            } elseif ($accountAction === 'delete') {
                $password = is_string($_POST['delete_password'] ?? null) ? $_POST['delete_password'] : '';
                if (!$sensitiveActionAllowed) {
                    $deleteModalError = 'Trop de tentatives. Réessayez dans 15 minutes.';
                    $deleteModalOpen = true;
                } elseif (!password_verify($password, $user['password'])) {
                    $deleteModalError = 'Le mot de passe est incorrect.';
                    $deleteModalOpen = true;
                } else {
                    if (!$this->userModel->deleteAccount($userId)) {
                        $deleteModalError = 'Le compte n’a pas pu être supprimé.';
                        $deleteModalOpen = true;
                    } else {
                        Security::logout($this->pdo);
                        session_start();
                        $_SESSION['site_notification'] = [
                            'type' => 'success',
                            'message' => 'Votre compte a bien été supprimé.',
                            'persistent' => false
                        ];
                        header('Location: /');
                        exit;
                    }
                }
            }

            $_SESSION['account_feedback'] = [
                'success' => $successMessage,
                'error' => $errorMessage,
                'email_error' => $emailModalError,
                'delete_error' => $deleteModalError,
                'new_email' => $emailModalError !== '' ? $newEmailValue : ''
            ];
            header('Location: /account');
            exit;
        }

        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/account.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }

    public function checkEmailAvailability(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false], 405);
        }
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false], 401);
        }

        Security::requireCsrf();
        $userId = (int)$_SESSION['user_id'];
        $limiter = new RateLimiter($this->pdo);
        if (!$limiter->allow(Security::identifierKey('account-email-availability', (string)$userId), 30, 600)) {
            $this->json(['success' => false, 'error' => 'Trop de vérifications. Réessayez dans quelques minutes.'], 429);
        }
        $email = Validation::email($_POST['email'] ?? '');
        if ($email === null) {
            $this->json(['success' => true, 'valid' => false, 'available' => false]);
        }

        $user = $this->userModel->getUserById($userId);
        $available = $user
            && mb_strtolower($email) !== mb_strtolower((string)$user['email'])
            && !$this->userModel->emailExistsForAnotherUser($email, $userId);
        $this->json(['success' => true, 'valid' => true, 'available' => $available]);
    }

    public function export(): void {
        Security::requireMethod('GET');
        $userId = $this->requireUser();
        $jobs = (new Job($this->pdo))->getUserJobs($userId);
        $statusLabels = [
            'JE_POSTULE' => 'Je postule',
            'POSTULE' => 'J’ai postulé',
            'RELANCE' => 'Je relance',
            'ENTRETIEN' => 'J’ai un entretien',
            'REFUSE' => 'Refusé'
        ];
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="pokejob-candidatures-' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'wb');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Intitulé du poste', 'Entreprise', 'Étape', 'Contact', 'Téléphone', 'E-mail du contact', 'Lien de l’annonce', 'LinkedIn', 'Site de l’entreprise', 'Date de candidature', 'Date de relance', 'Type', 'Notes'], ';', '"', '');
        foreach ($jobs as $job) {
            fputcsv($output, array_map([$this, 'csvCell'], [
                $job['job_title'] ?? '',
                $job['company_name'] ?? '',
                $statusLabels[$job['status'] ?? ''] ?? ($job['status'] ?? ''),
                $job['contact_name'] ?? '',
                $job['contact_phone'] ?? '',
                $job['contact_mail'] ?? '',
                $job['link_annonce'] ?? '',
                $job['link_linkedin'] ?? '',
                $job['company_website'] ?? '',
                $job['date_applied'] ?? '',
                $job['date_relance'] ?? '',
                match ($job['type_candidature'] ?? '') {
                    'ANNONCE' => 'Annonce',
                    'SPONTANEE' => 'Spontanée',
                    default => ''
                },
                $job['notes_perso'] ?? ''
            ]), ';', '"', '');
        }
        fclose($output);
        exit;
    }

    private function requireUser(): int {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        return (int)$_SESSION['user_id'];
    }

    private function csvCell(mixed $value): string {
        $text = (string)$value;
        return preg_match('/^[\x00-\x20]*[=+\-@]/u', $text) ? "'" . $text : $text;
    }

    private function json(array $payload, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
