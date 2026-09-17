<main class="container page-shell">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <h2 class="fw-bold text-center mb-4">Nous contacter</h2>

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

          <form action="/contact" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="first_name" name="first_name" maxlength="100" value="<?= htmlspecialchars($firstName ?? "") ?>" placeholder="Prénom" required>
              <label for="first_name">Prénom *</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="last_name" name="last_name" maxlength="100" value="<?= htmlspecialchars($lastName ?? "") ?>" placeholder="Nom" required>
              <label for="last_name">Nom *</label>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($email ?? "") ?>" placeholder="Adresse email" required>
              <label for="email">Adresse email *</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="subject" name="subject" maxlength="150" value="<?= htmlspecialchars($subject ?? "") ?>" placeholder="Sujet du message" required>
              <label for="subject">Sujet *</label>
            </div>
            <div class="form-floating mb-3">
              <textarea class="form-control" id="message" name="message" rows="5" maxlength="5000" placeholder="Décrivez votre demande" required><?= htmlspecialchars($message ?? "") ?></textarea>
              <label for="message">Message *</label>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary fw-bold">Envoyer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>
