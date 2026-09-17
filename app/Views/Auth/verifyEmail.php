<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4"><?= htmlspecialchars($verificationTitle, ENT_QUOTES, "UTF-8") ?></h2>
      <div class="card shadow">
        <div class="card-body">
          <p class="text-center">Saisissez le code à 6 chiffres envoyé à <strong><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?></strong>.</p>
          <?php if ($errorMessage): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
          <?php endif; ?>
          <?php if ($successMessage): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
          <?php endif; ?>
          <form method="post" action="/verify-email">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="verification_action" value="verify">
            <div class="form-floating mb-3">
              <input class="form-control verification-code" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Code" required>
              <label for="code">Code à 6 chiffres *</label>
            </div>
            <button class="btn btn-primary fw-bold w-100" type="submit">Confirmer mon e-mail</button>
          </form>
          <form method="post" action="/verify-email" class="text-center mt-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="verification_action" value="resend">
            <button class="verification-action-link" type="submit">Renvoyer le code</button>
          </form>
          <?php if ($canChangeEmail): ?>
            <div class="text-center mt-2">
              <button class="verification-action-link" type="button" data-bs-toggle="collapse" data-bs-target="#verificationEmailChange" aria-expanded="false" aria-controls="verificationEmailChange">Modifier l’adresse e-mail</button>
            </div>
            <div class="collapse mt-3" id="verificationEmailChange">
              <form method="post" action="/verify-email">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
                <input type="hidden" name="verification_action" value="change_email">
                <div class="form-floating mb-3">
                  <input type="email" class="form-control" id="verification_new_email" name="new_email" autocomplete="email" placeholder="Nouvelle adresse e-mail" required>
                  <label for="verification_new_email">Nouvelle adresse e-mail *</label>
                </div>
                <button class="btn btn-secondary fw-bold w-100" type="submit">Envoyer un code à cette adresse</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
