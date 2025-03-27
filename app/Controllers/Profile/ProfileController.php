<?php
namespace App\Controllers\Profile;

require_once __DIR__ . '/../../../app/Config/Database.php';
require_once __DIR__ . '/../../../app/Models/User.php';

$userModel       = new \User($pdo);
$success_message = '';
$error_message   = '';
$user_id         = $_SESSION['user_id'];
$user            = $userModel->getUserById($user_id);

if (!$user) {
    session_destroy();
    header('Location: /index.php?route=login');
    exit;
}

$first_name = $user['first_name'];
$last_name  = $user['last_name'];
$email      = $user['email'];
$photo      = !empty($user['photo']) ? $user['photo'] : '/assets/images/default-profile.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['profilePic']['tmp_name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName   = uniqid() . '_' . basename($_FILES['profilePic']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetPath)) {
            if ($photo !== '/assets/images/default-profile.png' && file_exists($_SERVER['DOCUMENT_ROOT'] . $photo)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $photo);
            }
            $photo = '/uploads/' . $fileName;
        } else {
            $error_message .= "Erreur lors de l'upload de la photo. ";
        }
    }

    if (!empty($_POST['deletePhoto'])) {
        if ($photo !== '/assets/images/default-profile.png') {
            $path = $_SERVER['DOCUMENT_ROOT'] . $photo;
            if (file_exists($path)) unlink($path);
        }
        $photo = '/assets/images/default-profile.png';
    }

    $new_email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : null;
    if (!$new_email || $userModel->emailExistsForAnotherUser($new_email, $user_id)) {
        $error_message .= "Email invalide ou déjà utilisé. ";
    } else {
        $email = $new_email;
    }

    // 🔐 Traitement du mot de passe
    $updatePassword = false;
    $currentPassword    = $_POST['currentPassword'] ?? '';
    $newPassword        = $_POST['newPassword'] ?? '';
    $confirmNewPassword = $_POST['confirmNewPassword'] ?? '';

    if (!empty($newPassword) || !empty($confirmNewPassword)) {
        if (!password_verify($currentPassword, $user['password'])) {
            $error_message .= "Le mot de passe actuel est incorrect. ";
        } else {
            if ($newPassword !== $confirmNewPassword) {
                $error_message .= "Les nouveaux mots de passe ne correspondent pas. ";
            } else {
                $hashedNewPass = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePassword = true;
            }
        }
    }

    if (empty($error_message)) {
        $updateSuccess = $updatePassword
            ? $userModel->updateEmailPasswordAndPhoto($user_id, $email, $hashedNewPass, $photo)
            : $userModel->updateEmailAndPhoto($user_id, $email, $photo);

        if ($updateSuccess) {
            $success_message = "Profil mis à jour avec succès.";
            $_SESSION['email'] = $email;
            $_SESSION['photo'] = $photo;
        } else {
            $error_message .= "Erreur lors de la mise à jour.";
        }
    }
}

require_once __DIR__ . '/../../../app/Views/Profile/profile.php';