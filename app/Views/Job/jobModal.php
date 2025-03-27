<div class="modal fade" id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content card modal-card">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="jobModalLabel">Titre de la modal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <form id="jobForm" method="post">
          <input type="hidden" id="modeField" name="mode">
          <input type="hidden" id="jobIdField" name="job_id">
          <input type="hidden" id="statusField" name="status">
          <div class="mb-3">
            <label for="company_name" class="form-label fw-bold">Nom de l'entreprise</label>
            <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Nom de l'entreprise" required>
          </div>
          <div class="mb-3">
            <label for="contact_name" class="form-label fw-bold">Contact (nom)</label>
            <input type="text" id="contact_name" name="contact_name" class="form-control" placeholder="Nom du contact">
          </div>
          <div class="mb-3">
            <label for="contact_phone" class="form-label fw-bold">Contact (téléphone)</label>
            <input type="text" id="contact_phone" name="contact_phone" class="form-control" placeholder="Téléphone">
          </div>
          <div class="mb-3">
            <label for="contact_mail" class="form-label fw-bold">Contact (email)</label>
            <input type="email" id="contact_mail" name="contact_mail" class="form-control" placeholder="Email">
          </div>
          <div class="mb-3">
            <label for="link_annonce" class="form-label fw-bold">Lien de l'annonce</label>
            <input type="url" id="link_annonce" name="link_annonce" class="form-control" placeholder="Lien de l'annonce">
          </div>
          <div class="mb-3">
            <label for="link_linkedin" class="form-label fw-bold">Lien LinkedIn</label>
            <input type="url" id="link_linkedin" name="link_linkedin" class="form-control" placeholder="Lien LinkedIn">
          </div>
          <div class="mb-3">
            <label for="date_applied" class="form-label fw-bold">Date de candidature</label>
            <input type="date" id="date_applied" name="date_applied" class="form-control">
          </div>
          <div class="mb-3">
            <label for="date_relance" class="form-label fw-bold">Date de relance</label>
            <input type="date" id="date_relance" name="date_relance" class="form-control">
          </div>
          <div class="mb-3">
            <label for="notes_perso" class="form-label fw-bold">Notes perso</label>
            <textarea id="notes_perso" name="notes_perso" class="form-control" rows="3" placeholder="Notes"></textarea>
          </div>
          <div class="mb-3">
            <label for="company_website" class="form-label fw-bold">Site de l'entreprise</label>
            <input type="url" id="company_website" name="company_website" class="form-control" placeholder="Site de l'entreprise">
          </div>
          <div class="mb-3">
            <label for="type_candidature" class="form-label fw-bold">Type de candidature</label>
            <select id="type_candidature" name="type_candidature" class="form-select">
              <option value="SPONTANEE">Spontanée</option>
              <option value="ANNONCE">Annonce</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <hr class="modal-separator">
        <div class="modal-buttons text-center w-100">
          <button type="submit" form="jobForm" id="saveBtn" class="btn btn-primary fw-bold btn-save" style="display:none;">Enregistrer</button>
        </div>
      </div>
    </div>
  </div>
</div>