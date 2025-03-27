<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content card modal-card">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalLabel">Liste des utilisateurs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <?php foreach ($users as $u): ?>
          <div class="user-label mb-2">
            <div class="user-info">
              <p class="user-name fw-bold"><?= htmlspecialchars($u['first_name']) . ' ' . htmlspecialchars($u['last_name']) ?></p>
              <p class="user-email"><?= htmlspecialchars($u['email']) ?></p>
            </div>
            <div class="user-action">
              <a href="#" 
                 data-href="/index.php?route=dashboard&action=delete_user&id=<?= htmlspecialchars($u['id']) ?>"
                 data-confirm="Voulez-vous vraiment supprimer cet utilisateur ?"
                 class="delete-cross confirm-action">
                 Supprimer
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>