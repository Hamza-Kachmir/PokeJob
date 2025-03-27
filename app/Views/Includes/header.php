<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PokéJob</title>
  <link rel="icon" type="image/png" href="/assets/images/logo-head.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.1/jquery-ui.min.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
  <script src="/assets/js/jobManagement.js" defer></script>
  <script src="/assets/js/script.js" defer></script>
</head>
<body>
<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

      <!-- Logo -->
      <a class="navbar-brand" href="/index.php?route=home">
        <img src="/assets/images/logo.png" alt="Logo" class="d-inline-block logo-navbar">
      </a>

      <!-- Hamburger -->
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <i class="fa-solid fa-bars"></i>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="/index.php?route=home">Accueil</a></li>
            <li class="nav-item"><a class="nav-link" href="/index.php?route=contact">Nous contacter</a></li>
            <li class="nav-item"><a class="nav-link" href="/index.php?route=login">Connexion</a></li>
          <?php else: ?>
            <?php $userRole = $_SESSION['user_role'] ?? 'USER'; ?>
            <?php if ($userRole === 'ADMIN'): ?>
              <li class="nav-item"><a class="nav-link" href="/index.php?route=dashboard">Dashboard</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="/index.php?route=jobs">Mes candidatures</a></li>
              <li class="nav-item"><a class="nav-link" href="/index.php?route=contact">Nous contacter</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="/index.php?route=profile">Mon profil</a></li>
            <li class="nav-item"><a class="nav-link" href="/index.php?route=logout">Déconnexion</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Dark Mode toggle -->
      <label for="theme" class="theme">
        <input id="theme" name="theme" class="toggle-checkbox" type="checkbox">
        <div class="toggle-slot">
          <div class="sun-icon-wrapper"><div class="iconify sun-icon" data-icon="feather-sun"></div></div>
          <div class="toggle-button"></div>
          <div class="moon-icon-wrapper"><div class="iconify moon-icon" data-icon="feather-moon"></div></div>
        </div>
      </label>

    </div>
  </nav>
</header>