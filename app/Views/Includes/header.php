<?php
$profileInitials = "";
if (isset($_SESSION["user_id"])) {
  $firstInitial = mb_substr(trim((string) ($_SESSION["first_name"] ?? ($_SESSION["user_name"] ?? ""))), 0, 1);
  $lastInitial = mb_substr(trim((string) ($_SESSION["last_name"] ?? "")), 0, 1);
  $profileInitials = mb_strtoupper($firstInitial . $lastInitial);
  if ($profileInitials === "") {
    $profileInitials = "PJ";
  }
}
$siteNotification = $_SESSION["site_notification"] ?? null;
unset($_SESSION["site_notification"]);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? "PokéJob", ENT_QUOTES, "UTF-8") ?></title>
  <meta name="csrf-token" content="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
  <script src="<?= htmlspecialchars(App\Support\Asset::url('/assets/js/theme-init.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
  <link rel="icon" type="image/png" href="<?= htmlspecialchars(App\Support\Asset::url('/assets/images/logo-head.png'), ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" integrity="sha384-qrALq7+6jBOZIQsNnT6xGkMDru64qD6uTlDra39xrt2SoXl4pO3FX6Roz/RpR/BS" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.3/jquery-ui.min.css" integrity="sha384-Wdf9be1Zb3KyYwg8A+RZyGXu7V52PYvfwVvmBllPV+Zt39d2+PnJpzm8v6ZEbIIW" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= htmlspecialchars(App\Support\Asset::url('/assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body>
  <header>
    <nav class="navbar navbar-expand-lg fixed-top" aria-label="Navigation principale">
      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a class="navbar-brand" href="/">
          <img src="<?= htmlspecialchars(App\Support\Asset::url('/assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="PokéJob" class="d-inline-block logo-navbar">
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Ouvrir le menu">
          <span class="navbar-animated" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
          </span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="/">Accueil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/dashboard">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/contact">Nous contacter</a>
            </li>
            <?php if (!isset($_SESSION["user_id"])): ?>
              <li class="nav-item desktop-login-item">
                <a class="nav-link" href="/login">Connexion</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <div class="navbar-actions">
          <?php if (isset($_SESSION["user_id"])): ?>
            <div class="dropdown profile-menu">
              <button class="profile-menu-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ouvrir le menu du profil">
                <span class="profile-avatar" aria-hidden="true"><?= htmlspecialchars($profileInitials, ENT_QUOTES, "UTF-8") ?></span>
                <i class="fa-solid fa-chevron-down profile-chevron" aria-hidden="true"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                <a class="profile-dropdown-item" href="/account">
                  <i class="fa-regular fa-circle-user" aria-hidden="true"></i>
                  <span>Mon compte</span>
                </a>
                <form method="post" action="/logout" class="profile-logout-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
                  <button type="submit" class="profile-dropdown-item">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                    <span>Me déconnecter</span>
                  </button>
                </form>
              </div>
            </div>
          <?php else: ?>
            <a class="mobile-account-link" href="/login" aria-label="Se connecter" title="Se connecter">
              <span class="profile-avatar" aria-hidden="true">
                <i class="fa-regular fa-user"></i>
              </span>
            </a>
          <?php endif; ?>
          <label for="theme" class="theme" title="Changer de thème">
            <input id="theme" name="theme" class="toggle-checkbox" type="checkbox">
            <span class="toggle-slot" aria-hidden="true">
              <i class="fa-solid fa-moon theme-icon theme-icon-moon"></i>
              <span class="theme-icon theme-icon-sun">☀</span>
            </span>
          </label>
        </div>

      </div>
    </nav>
  </header>
  <?php if (is_array($siteNotification) && !empty($siteNotification["message"])): ?>
    <div class="site-notification-layer" aria-live="polite">
      <div class="site-notification site-notification-<?= htmlspecialchars((string) ($siteNotification["type"] ?? "info"), ENT_QUOTES, "UTF-8") ?>" role="status" data-site-notification data-auto-dismiss="<?= !empty($siteNotification["persistent"]) ? "false" : "true" ?>">
        <span class="site-notification-icon" aria-hidden="true">
          <i class="fa-solid <?= ($siteNotification["type"] ?? "") === "success" ? "fa-circle-check" : (($siteNotification["type"] ?? "") === "warning" ? "fa-triangle-exclamation" : "fa-circle-info") ?>"></i>
        </span>
        <span class="site-notification-message"><?= htmlspecialchars((string) $siteNotification["message"], ENT_QUOTES, "UTF-8") ?></span>
        <button type="button" class="site-notification-close" data-site-notification-close aria-label="Fermer la notification">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  <?php endif; ?>
