<footer class="my-footer">
  <div class="container footer-layout">
    <p class="footer-main mb-0">
      &copy; <span id="currentYear"></span>
      <img src="<?= htmlspecialchars(App\Support\Asset::url('/assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="PokéJob" class="my-footer-logo">
      Développé par <a href="https://hamza-kachmir.github.io/Portfolio/" class="outfit-link" target="_blank" rel="noopener noreferrer">Hamza Kachmir</a>
    </p>
    <a href="/privacy" class="footer-privacy-link">Confidentialité</a>
  </div>
</footer>

<dialog class="pokejob-dialog" id="pokejobDialog" aria-labelledby="pokejobDialogTitle" aria-describedby="pokejobDialogMessage">
  <div class="modal-content card">
    <div class="modal-header pokejob-dialog-header">
      <span class="pokejob-dialog-icon info" data-dialog-icon aria-hidden="true">
        <i class="fa-solid fa-circle-info"></i>
      </span>
      <h5 class="modal-title fw-bold" id="pokejobDialogTitle" data-dialog-title>Information</h5>
      <button type="button" class="btn-close pokejob-dialog-close" data-dialog-close aria-label="Fermer"></button>
    </div>
    <div class="modal-body">
      <p class="mb-0" id="pokejobDialogMessage" data-dialog-message></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dialog-cancel>Annuler</button>
      <button type="button" class="btn btn-primary fw-bold" data-dialog-confirm>Confirmer</button>
    </div>
  </div>
</dialog>

<script src="https://code.jquery.com/jquery-4.0.0.min.js" integrity="sha384-fgGyf7Mo7DURSOMnOy7ed+dkq5Job205Gnzu6QIg0BOHKaqt4D76Dt8VlDCzcMHV" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.3/jquery-ui.min.js" integrity="sha384-oVpH0DXO9nadZxTmPSQo3YwWqfN/Up9aRDHCxLrw8A2LjkFNcM/XILw4KGMaL95z" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script src="<?= htmlspecialchars(App\Support\Asset::url('/assets/js/dialogs.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(App\Support\Asset::url('/assets/js/notifications.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(App\Support\Asset::url('/assets/js/forms.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(App\Support\Asset::url('/assets/js/jobManagement.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
<script src="<?= htmlspecialchars(App\Support\Asset::url('/assets/js/script.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>

</html>
