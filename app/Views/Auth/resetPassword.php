<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4">Nouveau mot de passe</h2>
      <div class="card shadow">
        <div class="card-body">
          <?php if ($errorMessage): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
          <?php endif; ?>
          <form method="post" action="/reset-password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" data-password-policy placeholder="Nouveau mot de passe" required>
              <label for="password">Nouveau mot de passe *</label>
            </div>
            <ul class="password-policy" data-password-checklist aria-live="polite">
              <li data-rule="length">Au moins 8 caractères</li>
              <li data-rule="uppercase">Au moins une majuscule</li>
              <li data-rule="digit">Au moins un chiffre</li>
              <li data-rule="special">Au moins un caractère spécial</li>
            </ul>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" data-password-confirm="password" placeholder="Confirmation" required>
              <label for="password_confirm">Confirmez le mot de passe *</label>
            </div>
            <p class="field-feedback" data-password-match aria-live="polite">Confirmez votre mot de passe.</p>
            <button class="btn btn-primary fw-bold w-100" type="submit" data-password-submit>Modifier le mot de passe</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>
