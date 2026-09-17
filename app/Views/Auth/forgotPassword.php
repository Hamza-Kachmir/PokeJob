<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4">Mot de passe oublié</h2>

      <div class="card shadow">
        <div class="card-body">
          <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
          <?php endif; ?>
          <p class="text-muted text-center small mb-3">
            Entrez votre adresse e-mail pour recevoir un code valable 10 minutes.
          </p>

          <form action="/forgot-password" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
              <label for="email">Adresse email *</label>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary fw-bold">Recevoir le code</button>
            </div>
          </form>

          <p class="mt-3 text-center mb-0">
            <a class="auth-link" href="/login">Retour à la connexion</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</main>
