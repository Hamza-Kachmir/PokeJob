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
          <h2 class="fw-bold card-title text-center mb-4">Inscription</h2>
          <form action="/index.php?route=register" method="post">
            <div class="mb-3">
              <label for="first_name" class="form-label fw-bold">Prénom</label>
              <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Entrez votre prénom" required>
            </div>
            <div class="mb-3">
              <label for="last_name" class="form-label fw-bold">Nom</label>
              <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Entrez votre nom" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Adresse email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Entrez votre email" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label fw-bold">Mot de passe</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" required>
            </div>
            <div class="mb-3">
              <label for="password_confirm" class="form-label fw-bold">Confirmez le mot de passe</label>
              <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Confirmez votre mot de passe" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">S'inscrire</button>
            </div>
          </form>
          <p class="mt-3 text-center">Déjà un compte ? <a href="/index.php?route=login">Connectez-vous</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
