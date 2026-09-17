<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10 col-xl-9">
      <h2 class="fw-bold text-center mb-4">Mon compte</h2>
      <?php if ($errorMessage): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
      <?php endif; ?>
      <?php if ($successMessage): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
      <?php endif; ?>
      <?php if ($emailModalOpen): ?>
        <span id="openEmailModal" hidden></span>
      <?php endif; ?>
      <?php if ($deleteModalOpen): ?>
        <span id="openDeleteAccountModal" hidden></span>
      <?php endif; ?>
      <div class="account-sections">
        <section class="card shadow">
          <div class="card-body account-card-body">
            <h4 class="fw-bold mb-3">Informations personnelles</h4>
            <div class="form-floating mb-3">
              <input class="form-control form-control-disabled" id="first_name" value="<?= htmlspecialchars($firstName, ENT_QUOTES, "UTF-8") ?>" placeholder="Prénom" readonly>
              <label for="first_name">Prénom</label>
            </div>
            <div class="form-floating mb-3">
              <input class="form-control form-control-disabled" id="last_name" value="<?= htmlspecialchars($lastName, ENT_QUOTES, "UTF-8") ?>" placeholder="Nom" readonly>
              <label for="last_name">Nom</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control form-control-disabled" id="current_account_email" value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>" placeholder="Adresse e-mail actuelle" readonly>
              <label for="current_account_email">Adresse e-mail actuelle</label>
            </div>
            <div class="form-floating mb-2">
              <input type="email" class="form-control account-email-control" id="account_email" name="new_email" form="emailUpdateForm" value="<?= htmlspecialchars($newEmailValue, ENT_QUOTES, "UTF-8") ?>" autocomplete="off" autocapitalize="none" spellcheck="false" pattern="[^\s@]+@[^\s@]+\.[^\s@]{2,}" placeholder="Nouvelle adresse e-mail" readonly required>
              <label for="account_email">Nouvelle adresse e-mail *</label>
            </div>
            <p class="field-feedback mb-3" data-account-email-feedback aria-live="polite"></p>
            <button type="button" class="btn btn-primary fw-bold mt-auto align-self-start" id="accountEmailButton" data-bs-toggle="modal" data-bs-target="#emailModal" disabled>Modifier l’adresse e-mail</button>
          </div>
        </section>
        <section class="card shadow">
          <div class="card-body account-card-body">
            <h4 class="fw-bold mb-3">Changer de mot de passe</h4>
            <form action="/account" method="post" class="account-card-form">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
              <input type="hidden" name="account_action" value="password">
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="currentPassword" name="currentPassword" autocomplete="current-password" placeholder="Mot de passe actuel" required>
                <label for="currentPassword">Mot de passe actuel *</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="newPassword" name="newPassword" autocomplete="new-password" minlength="8" maxlength="72" data-password-policy placeholder="Nouveau mot de passe" required>
                <label for="newPassword">Nouveau mot de passe *</label>
              </div>
              <ul class="password-policy" data-password-checklist aria-live="polite">
                <li data-rule="length">Au moins 8 caractères</li>
                <li data-rule="uppercase">Au moins une majuscule</li>
                <li data-rule="digit">Au moins un chiffre</li>
                <li data-rule="special">Au moins un caractère spécial</li>
              </ul>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" id="confirmNewPassword" name="confirmNewPassword" autocomplete="new-password" data-password-confirm="newPassword" placeholder="Confirmation" required>
                <label for="confirmNewPassword">Confirmez le nouveau mot de passe *</label>
              </div>
              <p class="field-feedback" data-password-match aria-live="polite">Confirmez votre nouveau mot de passe.</p>
              <button type="submit" class="btn btn-primary fw-bold mt-auto align-self-start" data-password-submit>Modifier le mot de passe</button>
            </form>
          </div>
        </section>
      </div>
      <section class="card shadow account-data-card">
        <div class="card-body">
          <h4 class="fw-bold mb-3">Vos données</h4>
          <p>Vous pouvez récupérer vos candidatures dans un fichier CSV compatible avec Excel.</p>
          <a class="btn btn-secondary fw-bold" href="/account?action=export">Exporter mes données</a>
          <hr>
          <h5 class="fw-bold">Supprimer mon compte</h5>
          <p>Cette action supprime définitivement votre compte et toutes vos candidatures.</p>
          <button type="button" class="btn btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Supprimer définitivement mon compte</button>
        </div>
      </section>
    </div>
  </div>
</main>

<div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="emailModalLabel">Modifier l’adresse e-mail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form id="emailUpdateForm" action="/account" method="post" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
          <input type="hidden" name="account_action" value="email">
          <?php if ($emailModalError): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($emailModalError, ENT_QUOTES, "UTF-8") ?></div>
          <?php endif; ?>
          <p>Confirmez cette modification avec votre mot de passe.</p>
          <div class="form-floating">
            <input type="password" class="form-control" id="current_password_email" name="current_password_email" autocomplete="new-password" placeholder="Mot de passe" required>
            <label for="current_password_email">Mot de passe *</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary fw-bold">Mettre à jour l’e-mail</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">Supprimer votre compte ?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <form action="/account" method="post">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
          <input type="hidden" name="account_action" value="delete">
          <?php if ($deleteModalError): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($deleteModalError, ENT_QUOTES, "UTF-8") ?></div>
          <?php endif; ?>
          <p>Cette action est définitive. Votre compte et toutes vos candidatures seront supprimés.</p>
          <div class="form-floating">
            <input type="password" class="form-control" id="delete_password" name="delete_password" autocomplete="current-password" placeholder="Mot de passe" required>
            <label for="delete_password">Mot de passe *</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Conserver mon compte</button>
          <button type="submit" class="btn btn-danger fw-bold">Supprimer définitivement</button>
        </div>
      </form>
    </div>
  </div>
</div>
