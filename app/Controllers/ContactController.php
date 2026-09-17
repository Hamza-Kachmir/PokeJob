<?php

namespace App\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Services\RateLimiter;
use App\Services\Mailer;
use App\Support\Security;
use App\Support\Validation;
use PDO;

class ContactController {
    private Contact $contactModel;
    private User $userModel;

    public function __construct(private PDO $pdo) {
        $this->contactModel = new Contact($pdo);
        $this->userModel = new User($pdo);
    }

    public function index(): void {
        $successMessage = $_SESSION['contact_success_message'] ?? '';
        unset($_SESSION['contact_success_message']);
        $errorMessage = '';
        $firstName = '';
        $lastName = '';
        $email = '';
        $subject = '';
        $message = '';

        if (isset($_SESSION['user_id'])) {
            $user = $this->userModel->getUserById((int)$_SESSION['user_id']);
            if ($user) {
                $firstName = $user['first_name'] ?? '';
                $lastName = $user['last_name'] ?? '';
                $email = $user['email'] ?? '';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();
            $firstName = Validation::text($_POST['first_name'] ?? '', 100, true);
            $lastName = Validation::text($_POST['last_name'] ?? '', 100, true);
            $email = Validation::email($_POST['email'] ?? '');
            $subject = Validation::text($_POST['subject'] ?? '', 150, true);
            $message = Validation::text($_POST['message'] ?? '', 5000, true);

            if ($firstName === null || $lastName === null || $email === null || $subject === null || $message === null) {
                $errorMessage = 'Veuillez vérifier les informations saisies.';
            } else {
                $limiter = new RateLimiter($this->pdo);
                $allowedForIdentity = $limiter->allow(Security::identifierKey('contact', $email), 5, 3600);
                $allowedForIp = $limiter->allow(Security::clientKey('contact-ip'), 12, 3600);
                if (!$allowedForIdentity || !$allowedForIp) {
                    $errorMessage = 'Trop de messages ont été envoyés. Réessayez plus tard.';
                } else {
                    try {
                        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
                        $this->pdo->beginTransaction();
                        if (!$this->contactModel->addContact($userId, $firstName, $lastName, $email, $subject, $message)) {
                            throw new \RuntimeException('Enregistrement du message impossible.');
                        }
                        $this->pdo->commit();
                        try {
                            (new Mailer())->sendContactMessage($firstName, $lastName, $email, $subject, $message);
                        } catch (\Throwable $exception) {
                            error_log($exception->getMessage());
                            $_SESSION['contact_success_message'] = 'Votre message a été enregistré, mais sa notification n’a pas pu être envoyée. Nous vous invitons à réessayer plus tard si votre demande est urgente.';
                            header('Location: /contact');
                            exit;
                        }
                        $_SESSION['contact_success_message'] = 'Merci pour votre message, nous vous répondrons au plus vite.';
                        header('Location: /contact');
                        exit;
                    } catch (\Throwable $exception) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        error_log($exception->getMessage());
                        $errorMessage = 'Une erreur est survenue lors de l’envoi du message.';
                    }
                }
            }
        }

        $pageTitle = 'Contact - PokéJob';
        require ROOT_PATH . '/app/Views/Includes/header.php';
        require ROOT_PATH . '/app/Views/contact.php';
        require ROOT_PATH . '/app/Views/Includes/footer.php';
    }
}
