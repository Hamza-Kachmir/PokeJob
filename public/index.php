<?php
session_start();

require_once __DIR__ . '/../app/Config/Database.php';

$route  = $_GET['route']  ?? 'home';
$action = $_GET['action'] ?? 'index';

// Détecte les actions renvoyant du JSON pour AJAX (voir détails / update_status)
$isJsonAction = ($route === 'jobs' && in_array($action, ['show', 'update_status']));

// Démarrer le buffer seulement si on n'attend pas de JSON
if (!$isJsonAction) {
    ob_start();
}

switch ($route) {
    case 'home':
        if (isset($_SESSION['user_id'])) {
            header('Location: /index.php?route=' . (($_SESSION['user_role'] ?? '') === 'ADMIN' ? 'dashboard' : 'jobs'));
            exit;
        }
        require_once __DIR__ . '/../app/Views/homepage.php';
        break;

    case 'login':
        require_once __DIR__ . '/../app/Controllers/Auth/LoginController.php';
        break;

    case 'register':
        require_once __DIR__ . '/../app/Controllers/Auth/RegisterController.php';
        break;

    case 'logout':
        require_once __DIR__ . '/../app/Controllers/Auth/LogoutController.php';
        break;

    case 'forgot-password':
        require_once __DIR__ . '/../app/Controllers/Auth/ForgotPasswordController.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/../app/Controllers/Dashboard/DashboardController.php';
        $controller = new \App\Controllers\Dashboard\DashboardController($pdo);
        if ($action === 'delete_user') {
            $controller->deleteUser();
        } elseif ($action === 'reset_companies') {
            $controller->resetCompanies();
        } else {
            $controller->index();
        }
        break;

    case 'profile':
        require_once __DIR__ . '/../app/Controllers/Profile/ProfileController.php';
        break;

    case 'contact':
        require_once __DIR__ . '/../app/Controllers/Contact/ContactController.php';
        break;

    case 'jobs':
        require_once __DIR__ . '/../app/Controllers/Job/JobController.php';
        $controller = new \App\Controllers\Job\JobController($pdo);
        switch ($action) {
            case 'store':         $controller->store(); break;
            case 'update_status': $controller->updateStatus(); break;
            case 'show':          $controller->show(); break;
            case 'update':        $controller->update(); break;
            case 'delete':        $controller->destroy(); break;
            default:              $controller->index(); break;
        }
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/../app/Views/404.php';
        break;
}

if (!$isJsonAction) {
    $content = ob_get_clean();
    require_once __DIR__ . '/../app/Views/Includes/header.php';
    echo $content;
    require_once __DIR__ . '/../app/Views/Includes/footer.php';
} else {
    exit;
}
