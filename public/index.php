<?php

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

App\Config\Environment::load(ROOT_PATH);
App\Support\Security::bootstrap();

use App\Config\Database;
use App\Controllers\AccountController;
use App\Controllers\Auth\ForgotPasswordController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Auth\VerifyEmailController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Support\Maintenance;
use App\Support\Security;

$route = is_string($_GET['route'] ?? null) ? $_GET['route'] : 'home';
$action = is_string($_GET['action'] ?? null) ? $_GET['action'] : '';

// Toutes les requêtes passent par ce point d’entrée avant d’être confiées au contrôleur adapté
try {
    $pdo = null;
    $database = static function () use (&$pdo): PDO {
        if (!$pdo instanceof PDO) {
            $pdo = Database::getConnection();
            Maintenance::runDailyCleanup($pdo);
        }
        return $pdo;
    };

    if (isset($_SESSION['user_id']) || Security::hasRememberedLogin()) {
        $authenticatedPdo = $database();
        Security::resumeRememberedLogin($authenticatedPdo);
        Security::validateAuthenticatedSession($authenticatedPdo);
    }
    if (!empty($_SESSION['session_expired'])) {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['site_notification'] = [
                'type' => 'warning',
                'message' => 'Votre session a expiré.',
                'persistent' => true
            ];
        }
        unset($_SESSION['session_expired']);
    }

    switch ($route) {
        case 'home':
            $pageTitle = 'Accueil - PokéJob';
            require ROOT_PATH . '/app/Views/Includes/header.php';
            require ROOT_PATH . '/app/Views/homepage.php';
            require ROOT_PATH . '/app/Views/Includes/footer.php';
            break;
        case 'login':
            (new LoginController())->handleLogin();
            break;
        case 'register':
            $controller = new RegisterController();
            if ($action === 'check_email') {
                $controller->checkEmailAvailability();
            }
            $controller->handleRegister();
            break;
        case 'verify-email':
            (new VerifyEmailController())->handle();
            break;
        case 'forgot-password':
            (new ForgotPasswordController())->requestCode();
            break;
        case 'verify-reset-code':
            (new ForgotPasswordController())->verifyCode();
            break;
        case 'reset-password':
            (new ForgotPasswordController())->resetPassword();
            break;
        case 'contact':
            (new ContactController($database()))->index();
            break;
        case 'privacy':
            $pageTitle = 'Confidentialité - PokéJob';
            require ROOT_PATH . '/app/Views/Includes/header.php';
            require ROOT_PATH . '/app/Views/privacy.php';
            require ROOT_PATH . '/app/Views/Includes/footer.php';
            break;
        case 'account':
            $controller = new AccountController($database());
            if ($action === 'check_email') {
                $controller->checkEmailAvailability();
            }
            if ($action === 'export') {
                $controller->export();
                break;
            }
            $pageTitle = 'Mon compte - PokéJob';
            $controller->index();
            break;
        case 'logout':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                break;
            }
            Security::requireCsrf();
            Security::logout($database());
            session_start();
            $_SESSION['site_notification'] = [
                'type' => 'info',
                'message' => 'Vous êtes maintenant déconnecté.',
                'persistent' => false
            ];
            header('Location: /');
            exit;
        case 'dashboard':
            $controller = new DashboardController($database());
            match ($action) {
                'store' => $controller->store(),
                'update_status' => $controller->updateStatus(),
                'show' => $controller->show(),
                'delete' => $controller->destroy(),
                'delete_all' => $controller->destroyAll(),
                'update' => $controller->update(),
                'check_duplicate' => $controller->checkDuplicate(),
                'import_guest_jobs' => $controller->importGuestJobs(),
                default => $action === '' ? null : http_response_code(404)
            };
            if ($action === '') {
                $pageTitle = 'Dashboard - PokéJob';
                require ROOT_PATH . '/app/Views/Includes/header.php';
                $controller->index();
                require ROOT_PATH . '/app/Views/Includes/footer.php';
            }
            break;
        default:
            http_response_code(404);
            $pageTitle = 'Page introuvable - PokéJob';
            require ROOT_PATH . '/app/Views/Includes/header.php';
            echo '<main class="container page-shell text-center"><h1>404</h1><p>Cette page n’existe pas.</p><a href="/">Retour à l’accueil</a></main>';
            require ROOT_PATH . '/app/Views/Includes/footer.php';
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo 'Une erreur interne est survenue.';
}
