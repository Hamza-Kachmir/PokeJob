<div class="container mt-5">
  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success text-center">
      <?= htmlspecialchars($success_message) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger text-center">
      <?= htmlspecialchars($error_message) ?>
    </div>
  <?php endif; ?>

  <?php
    $statusLabels = [
      'JE_POSTULE'            => 'Je postule',
      'POSTULE'               => 'J’ai postulé',
      'RELANCE'               => 'Je relance',
      'PREPARATION_ENTRETIEN' => 'J’ai un entretien',
      'ENTRETIEN_REALISE'     => 'Entretien réalisé',
      'REFUSE'                => 'Refusé'
    ];

    $kanban = [
      'JE_POSTULE'            => [],
      'POSTULE'               => [],
      'RELANCE'               => [],
      'PREPARATION_ENTRETIEN' => [],
      'ENTRETIEN_REALISE'     => [],
      'REFUSE'                => []
    ];

    if (!isset($jobs) || !is_array($jobs)) {
      $jobs = [];
    }
    foreach ($jobs as $job) {
      $st = $job['status'] ?? 'JE_POSTULE';
      $kanban[$st][] = $job;
    }

    $first_name = $_SESSION['first_name'] ?? 'Utilisateur';
    $last_name = $_SESSION['last_name'] ?? '';
    $fullName = trim($first_name . ' ' . $last_name);
    $photo = $_SESSION['photo'] ?? '/assets/images/default-profile.png';
  ?>

  <div class="card my-3 welcome-card">
    <h2 class="fw-bold text-center mb-4">Mes candidatures</h2>
    <div class="welcome-photo text-center">
      <img src="<?= htmlspecialchars($photo) ?>" alt="Photo de profil">
    </div>
    <div class="welcome-text text-center mt-3">
      <h5>Bonjour <strong><?= htmlspecialchars($fullName) ?></strong></h5>
      <p>Bienvenue sur votre tableau de bord. Ici, vous pouvez gérer et suivre vos candidatures.</p>
    </div>
    <div class="welcome-actions mt-3 text-center">
      <div class="search-wrapper d-inline-block">
        <input type="text" id="searchCompany" placeholder="Chercher une entreprise" class="form-control">
        <span class="search-icon">
          <i class="fa fa-search"></i>
        </span>
      </div>
      <button id="deleteAllJobs" class="btn btn-danger ms-3 fw-bold confirm-action" 
              data-confirm="Voulez-vous vraiment supprimer toutes vos candidatures ?"
              data-callback="resetAllJobs">
        Reset
      </button>
    </div>
  </div>

  <div class="row status-cards">
    <?php foreach ($kanban as $statusKey => $jobList): ?>
      <div class="col-12 col-md-6 col-lg-2">
        <div class="card mb-3">
          <div class="card-header text-center fw-bold status-label">
            <?= htmlspecialchars($statusLabels[$statusKey] ?? $statusKey) ?>
          </div>
          <div class="card-body min-height-col connectedSortable" data-status="<?= htmlspecialchars($statusKey) ?>">
            <div class="fw-bold new-text mb-2"
                 data-bs-toggle="modal"
                 data-bs-target="#jobModal"
                 data-mode="new"
                 data-status="<?= htmlspecialchars($statusKey) ?>">
              Nouveau
            </div>
            <?php foreach ($jobList as $jobItem): ?>
              <div class="border mb-2 draggable-item" data-id="<?= htmlspecialchars($jobItem['id']) ?>">
                <div class="company-label">
                  <span class="fw-bold company-name">
                    <?= htmlspecialchars($jobItem['company_name']) ?>
                  </span>
                  <a href="#" 
                     data-href="/index.php?route=jobs&action=delete&id=<?= htmlspecialchars($jobItem['id']) ?>"
                     data-confirm="Voulez-vous vraiment supprimer cette candidature ?"
                     class="delete-cross confirm-action">
                    ✖
                  </a>
                </div>
                <div class="detail-container">
                  <span class="fw-bold detail-text"
                        data-id="<?= htmlspecialchars($jobItem['id']) ?>"
                        data-mode="detail"
                        data-bs-toggle="modal"
                        data-bs-target="#jobModal">
                    Voir les détails
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/jobModal.php'; ?>