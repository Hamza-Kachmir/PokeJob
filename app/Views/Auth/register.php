<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4">Inscription</h2>

      <div class="card shadow">
        <div class="card-body">
          <?php if (!empty($errorMessage)): ?>
            <div class="mb-3">
              <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
            </div>
          <?php endif; ?>

          <form action="/register" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="first_name" name="first_name" maxlength="50" value="<?= htmlspecialchars((string) $firstName, ENT_QUOTES, "UTF-8") ?>" placeholder="Entrez votre prénom" required>
              <label for="first_name">Prénom *</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="last_name" name="last_name" maxlength="50" value="<?= htmlspecialchars((string) $lastName, ENT_QUOTES, "UTF-8") ?>" placeholder="Entrez votre nom" required>
              <label for="last_name">Nom *</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= htmlspecialchars((string) $email, ENT_QUOTES, "UTF-8") ?>" placeholder="Entrez votre email" autocomplete="email" data-email-availability required>
              <label for="email">Adresse email *</label>
            </div>
            <p class="field-feedback" data-email-feedback aria-live="polite"></p>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" autocomplete="new-password" minlength="8" maxlength="72" data-password-policy required>
              <label for="password">Mot de passe *</label>
            </div>
            <ul class="password-policy" data-password-checklist aria-live="polite">
              <li data-rule="length">Au moins 8 caractères</li>
              <li data-rule="uppercase">Au moins une majuscule</li>
              <li data-rule="digit">Au moins un chiffre</li>
              <li data-rule="special">Au moins un caractère spécial</li>
            </ul>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password_confirm" name="password_confirm" autocomplete="new-password" data-password-confirm="password" placeholder="Confirmez votre mot de passe" required>
              <label for="password_confirm">Confirmez le mot de passe *</label>
            </div>
            <p class="field-feedback" data-password-match aria-live="polite">Confirmez votre mot de passe.</p>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary fw-bold" data-password-submit>S'inscrire</button>
            </div>
          </form>

          <p class="mt-3 text-center mb-0">Déjà un compte ? <a class="auth-link" href="/login">Connectez-vous</a></p>
        </div>
      </div>
    </div>
  </div>
</main>
