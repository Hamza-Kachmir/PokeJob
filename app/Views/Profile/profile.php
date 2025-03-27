<div class="container mt-5">
  <div class="row justify-content-center">
    <!-- Carte élargie pour une meilleure lisibilité -->
    <div class="col-md-7 col-lg-4">
      <div class="card shadow">
        <div class="card-body">
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center">
              <?= htmlspecialchars($error_message) ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success text-center">
              <?= htmlspecialchars($success_message) ?>
            </div>
          <?php endif; ?>

          <h2 class="fw-bold text-center mb-4">Mon Profil</h2>
          
          <form action="/index.php?route=profile" method="post" enctype="multipart/form-data">
            <div class="mb-4 text-center position-relative">
              <?php 
                // Affiche la photo de profil ou une photo par défaut
                $photoSrc = !empty($photo) ? htmlspecialchars($photo) : '/assets/images/default-profile.png'; 
              ?>
              <img id="photoPreview" src="<?= $photoSrc ?>" alt="Photo de profil" class="profile-pic mb-3">
              
              <!-- Overlay pour sélectionner un fichier -->
              <label for="profilePic" class="position-absolute top-50 start-50 translate-middle file-upload-overlay fw-bold">+</label>
              <input type="file" id="profilePic" name="profilePic" class="d-none" accept="image/*">
            </div>

            <!-- Bouton de suppression de photo si une photo personnalisée est définie -->
            <?php if (!empty($photo) && $photo !== '/assets/images/default-profile.png'): ?>
              <input type="checkbox" id="deletePhoto" name="deletePhoto" value="1" class="d-none">
              <div class="text-center mb-3">
                <button type="button" class="btn btn-danger fw-bold confirm-action" id="deletePhotoBtn" data-callback="deleteProfilePhoto">
                  Supprimer la photo
                </button>
              </div>
            <?php endif; ?>

            <!-- Champs de formulaire -->
            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Adresse email</label>
              <input type="email" class="form-control" id="email" name="email"
                     value="<?= htmlspecialchars($email) ?>" 
                     placeholder="Entrez la nouvelle adresse email" 
                     autocomplete="email" required>
            </div>

            <div class="mb-3">
              <label for="currentPassword" class="form-label fw-bold">Mot de passe actuel</label>
              <input type="password" class="form-control" id="currentPassword" name="currentPassword"
                     placeholder="Entrez le mot de passe" autocomplete="current-password">
            </div>

            <div class="mb-3">
              <label for="newPassword" class="form-label fw-bold">Nouveau mot de passe</label>
              <input type="password" class="form-control" id="newPassword" name="newPassword"
                     autocomplete="new-password" placeholder="Nouveau mot de passe">
            </div>

            <div class="mb-4">
              <label for="confirmNewPassword" class="form-label fw-bold">Confirmez le nouveau mot de passe</label>
              <input type="password" class="form-control" id="confirmNewPassword" name="confirmNewPassword"
                     autocomplete="new-password" placeholder="Confirmez votre nouveau mot de passe">
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
