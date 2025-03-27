<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="card shadow">
        <div class="card-body">
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error_message) ?></div>
          <?php endif; ?>
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success_message) ?></div>
          <?php endif; ?>
          <h2 class="fw-bold card-title text-center mb-4">Mot de passe oublié</h2>
          <form action="/index.php?route=forgot-password" method="post">
            <div class="mb-3">
              <label for="first_name" class="form-label fw-bold">Prénom</label>
              <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Votre prénom" required>
            </div>
            <div class="mb-3">
              <label for="last_name" class="form-label fw-bold">Nom</label>
              <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Votre nom" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Adresse email</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Envoyer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
