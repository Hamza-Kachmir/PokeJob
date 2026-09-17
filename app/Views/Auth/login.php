<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4">Connexion</h2>

      <div class="card shadow">
        <div class="card-body">
          <?php if (!empty($successMessage) || !empty($errorMessage)): ?>
            <div class="mb-3">
              <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
              <?php endif; ?>
              <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <form action="/login" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>" placeholder="Entrez votre email" autocomplete="email" required>
              <label for="email">Adresse email *</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" autocomplete="current-password" required>
              <label for="password">Mot de passe *</label>
            </div>
            <div class="form-check auth-remember mb-3">
              <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember_me" <?= $remember ? " checked" : "" ?>>
              <label class="form-check-label" for="remember_me">Rester connecté</label>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary fw-bold">Connexion</button>
            </div>
          </form>

          <p class="mt-3 text-center mb-1">Pas de compte ? <a class="auth-link" href="/register">Inscrivez-vous</a></p>
          <p class="mt-2 text-center mb-0"><a class="auth-link" href="/forgot-password">Mot de passe oublié ?</a></p>
        </div>
      </div>
    </div>
  </div>
</main>
