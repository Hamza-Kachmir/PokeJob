<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4">Vérifiez le code</h2>
      <div class="card shadow">
        <div class="card-body">
          <p class="text-center">Si cette adresse existe, un code à 6 chiffres a été envoyé. Il reste valable 10 minutes.</p>
          <?php if ($errorMessage): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
          <?php endif; ?>
          <form method="post" action="/verify-reset-code">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <div class="form-floating mb-3">
              <input class="form-control verification-code" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Code" required>
              <label for="code">Code à 6 chiffres *</label>
            </div>
            <button class="btn btn-primary fw-bold w-100" type="submit">Continuer</button>
          </form>
          <p class="text-center mt-3 mb-0"><a class="auth-link" href="/forgot-password">Demander un autre code</a></p>
        </div>
      </div>
    </div>
  </div>
</main>
