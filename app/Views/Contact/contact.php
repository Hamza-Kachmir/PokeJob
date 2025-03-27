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
          <h2 class="fw-bold card-title text-center mb-4">Nous contacter</h2>
          <form action="/index.php?route=contact" method="post">
            <div class="mb-3">
              <label for="first_name" class="form-label fw-bold">Prénom</label>
              <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($first_name) ?>" placeholder="Prénom" required>
            </div>
            <div class="mb-3">
              <label for="last_name" class="form-label fw-bold">Nom</label>
              <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($last_name) ?>" placeholder="Nom" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Adresse email</label>
              <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Adresse email" required>
            </div>
            <div class="mb-3">
              <label for="subject" class="form-label fw-bold">Sujet</label>
              <input type="text" class="form-control" id="subject" name="subject" placeholder="Sujet du message" required>
            </div>
            <div class="mb-3">
              <label for="message" class="form-label fw-bold">Message</label>
              <textarea class="form-control" id="message" name="message" rows="5" placeholder="Décrivez votre demande" required></textarea>
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