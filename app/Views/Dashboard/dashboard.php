<div class="container mt-5">
  <?php
    $admin_photo = $_SESSION['photo'] ?? '/assets/images/default-profile.png';
    $admin_first = $_SESSION['first_name'] ?? 'Admin';
    $admin_last = $_SESSION['last_name'] ?? '';
    $admin_name = trim($admin_first . ' ' . $admin_last);
  ?>
  <div class="card shadow p-3 mb-4 welcome-card">
    <h2 class="fw-bold text-center mb-4">Dashboard</h2>
    <div class="welcome-photo text-center">
      <img src="<?= htmlspecialchars($admin_photo) ?>" alt="Photo admin" class="profile-pic">
    </div>
    <div class="welcome-text text-center mt-3">
      <h5>Bonjour <strong><?= htmlspecialchars($admin_name) ?></strong> </h5>
      <p>Bienvenue sur votre tableau de bord admin.</p>
    </div>
  </div>

  <div class="row mb-4">
    <!-- Colonne Utilisateurs -->
    <div class="col-md-4 mb-3">
      <div class="card text-center h-100">
        <div class="card-header fw-bold">Nombre d’utilisateurs</div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center">
          <h2 class="text-center fw-bold"><?= htmlspecialchars($countUsers) ?></h2>
          <button type="button" class="btn btn-primary mt-2 fw-bold" data-bs-toggle="modal" data-bs-target="#userModal">
            Afficher
          </button>
        </div>
      </div>
    </div>
    <!-- Colonne Entreprises -->
    <div class="col-md-4 mb-3">
      <div class="card text-center h-100">
        <div class="card-header fw-bold">Nombre d’entreprises</div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center">
          <h2 class="text-center fw-bold"><?= htmlspecialchars($countCompanies) ?></h2>
          <button type="button" class="btn btn-danger mt-2 fw-bold confirm-action" 
                  data-href="/index.php?route=dashboard&action=reset_companies" 
                  data-confirm="Voulez-vous vraiment réinitialiser le compteur ?">
            Reset
          </button>
        </div>
      </div>
    </div>
    <!-- Colonne Messages non lus -->
    <div class="col-md-4 mb-3">
      <div class="card text-center h-100">
        <div class="card-header fw-bold">Messages non lus</div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center">
          <h2 class="text-center fw-bold"><?= htmlspecialchars($countUnreadMessages) ?></h2>
          <button type="button" class="btn btn-primary mt-2 fw-bold" onclick="window.location.href='pokejob:'">
            Ouvrir l'application
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/usersModal.php'; ?>
</div>