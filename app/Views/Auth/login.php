<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="card shadow">
        <div class="card-body">
          <?php if (!empty($success_message) || !empty($error_message)): ?>
            <div class="mb-3">
              <?php if (!empty($success_message)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($success_message) ?></div>
              <?php endif; ?>
              <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error_message) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <h2 class="fw-bold card-title text-center mb-4">Connexion</h2>
          <form action="/index.php?route=login" method="post">
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Adresse email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Entrez votre email" autocomplete="email" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label fw-bold">Mot de passe</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" autocomplete="current-password" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Connexion</button>
            </div>
          </form>
          <p class="mt-3 text-center">Pas de compte ? <a href="/index.php?route=register">Inscrivez-vous</a></p>
          <p class="mt-2 text-center"><a href="/index.php?route=forgot-password">Mot de passe oublié ?</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
