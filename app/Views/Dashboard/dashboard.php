<main class="container page-shell" id="dashboardApp" data-authenticated="<?= isset($_SESSION["user_id"]) ? "true" : "false" ?>" data-job-limit="<?= App\Models\Job::MAX_JOBS_PER_USER ?>">
  <h2 class="fw-bold text-center mb-4">Dashboard</h2>

  <?php if (!empty($jobLimitReached)): ?>
    <span id="jobLimitReached" hidden></span>
  <?php endif; ?>

  <?php if (!isset($_SESSION["user_id"])): ?>
    <div class="guest-notice" role="status">
      <i class="fa-solid fa-laptop" aria-hidden="true"></i>
      <div>
        <strong>Mode invité</strong>
        <p>Vos candidatures sont enregistrées uniquement dans ce navigateur. Créez un compte pour les retrouver sur tous vos appareils.</p>
      </div>
      <a href="/register" class="btn btn-primary">Créer un compte</a>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger text-center">
      <?= htmlspecialchars($errorMessage) ?>
    </div>
  <?php endif; ?>

  <?php
  $statusLabels = [
    "JE_POSTULE" => "Je postule",
    "POSTULE" => "J’ai postulé",
    "RELANCE" => "Je relance",
    "ENTRETIEN" => "J’ai un entretien",
    "REFUSE" => "Refusé",
  ];

  $kanban = [
    "JE_POSTULE" => [],
    "POSTULE" => [],
    "RELANCE" => [],
    "ENTRETIEN" => [],
    "REFUSE" => [],
  ];

  if (!isset($jobs) || !is_array($jobs)) {
    $jobs = [];
  }
  foreach ($jobs as $job) {
    $st = $job["status"] ?? "JE_POSTULE";
    if (isset($kanban[$st])) {
      $kanban[$st][] = $job;
    }
  }

  $firstName = trim($_SESSION["first_name"] ?? ($_SESSION["user_name"] ?? ""));
  ?>

  <div class="card my-3 welcome-card">
    <div class="welcome-text text-center mt-2">
      <h5>Bonjour<?= $firstName !== "" ? " <strong>" . htmlspecialchars($firstName) . "</strong>" : "" ?> !</h5>
      <p class="mb-0">Bienvenue sur votre tableau de bord. Ici, vous pouvez gérer et suivre vos candidatures.</p>
    </div>
    <div class="welcome-actions mt-3 text-center">
      <div class="search-wrapper form-floating d-inline-block">
        <input type="text" id="searchCompany" placeholder="Chercher une entreprise" class="form-control">
        <label for="searchCompany">Chercher une entreprise</label>
        <span class="search-icon">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        </span>
        <button type="button" id="clearCompanySearch" class="search-clear" aria-label="Effacer la recherche">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>
      <button id="deleteAllJobs" class="btn btn-danger ms-3 fw-bold confirm-action" data-confirm="Voulez-vous vraiment supprimer toutes vos candidatures ?" data-callback="resetAllJobs">
        Supprimer toutes les candidatures
      </button>
    </div>
  </div>

  <div class="row status-cards justify-content-center">
    <?php foreach ($kanban as $statusKey => $jobList): ?>
      <div class="col-12 col-md-6 col-lg-2">
        <div class="card mb-3">
          <div class="card-header text-center fw-bold">
            <?= htmlspecialchars($statusLabels[$statusKey] ?? $statusKey) ?>
          </div>
          <div class="card-body min-height-col connectedSortable" data-status="<?= htmlspecialchars($statusKey) ?>">
            <button type="button" class="fw-bold new-text mb-2" data-bs-toggle="modal" data-bs-target="#jobModal" data-mode="new" data-status="<?= htmlspecialchars($statusKey) ?>">
              Ajouter +
            </button>
            <?php foreach ($jobList as $jobItem): ?>
              <div class="border mb-2 draggable-item" data-id="<?= htmlspecialchars($jobItem["id"]) ?>">
                <div class="company-label">
                  <span class="fw-bold company-name">
                    <?= htmlspecialchars($jobItem["job_title"] ?? "Poste non renseigné") ?>
                  </span>
                  <button type="button" data-id="<?= htmlspecialchars($jobItem["id"]) ?>" data-confirm="Voulez-vous vraiment supprimer cette candidature ?" class="delete-cross server-delete" aria-label="Supprimer cette candidature">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                  </button>
                </div>
                <div class="text-muted small mb-1 px-2 company-meta">
                  <?= htmlspecialchars($jobItem["company_name"]) ?>
                </div>
                <div class="detail-container">
                  <button type="button" class="fw-bold detail-text" data-id="<?= htmlspecialchars($jobItem["id"]) ?>" data-mode="detail" data-bs-toggle="modal" data-bs-target="#jobModal">
                    Voir les détails
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<?php require_once __DIR__ . "/jobModal.php"; ?>
<?php if (isset($_SESSION["user_id"])): ?>
  <div class="modal fade" id="guestImportModal" tabindex="-1" aria-labelledby="guestImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="guestImportModalLabel">Retrouver vos candidatures locales</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <p id="guestImportMessage" class="mb-0"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="discardGuestJobs">Ne pas importer</button>
          <button type="button" class="btn btn-primary fw-bold" id="importGuestJobs">Importer</button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
