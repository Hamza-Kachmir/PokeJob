<div class="modal fade job-modal" id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable job-modal-dialog">
    <div class="modal-content card job-modal-content">
      <div class="modal-header job-modal-header">
        <div class="job-modal-heading">
          <span class="job-modal-icon" aria-hidden="true">
            <i class="fa-solid fa-briefcase"></i>
          </span>
          <div>
            <span class="job-modal-kicker" data-job-modal-kicker>Nouvelle candidature</span>
            <h5 class="modal-title fw-bold" id="jobModalLabel">Je postule</h5>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <div class="modal-body job-modal-body">
        <form id="jobForm" action="/dashboard?action=store" method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(App\Support\Security::csrfToken(), ENT_QUOTES, "UTF-8") ?>">
          <input type="hidden" id="allowDuplicateField" name="allow_duplicate" value="0">
          <input type="hidden" id="modeField" name="mode">
          <input type="hidden" id="jobIdField" name="job_id">
          <input type="hidden" id="statusField" name="status">

          <section class="job-form-section" aria-labelledby="jobPrimarySection">
            <h6 class="job-form-section-title" id="jobPrimarySection">
              <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
              Informations principales
            </h6>
            <div class="job-form-grid">
              <div class="form-floating">
                <input type="text" id="job_title" name="job_title" class="form-control" maxlength="150" placeholder="Ex: Développeur PHP" required>
                <label for="job_title">Intitulé du poste *</label>
              </div>
              <div class="form-floating">
                <input type="text" id="company_name" name="company_name" class="form-control" maxlength="100" placeholder="Nom de l'entreprise" required>
                <label for="company_name">Nom de l'entreprise *</label>
              </div>
            </div>
          </section>

          <section class="job-form-section" aria-labelledby="jobContactSection">
            <h6 class="job-form-section-title" id="jobContactSection">
              <i class="fa-regular fa-address-card" aria-hidden="true"></i>
              Contact
            </h6>
            <div class="job-form-grid">
              <div class="form-floating">
                <input type="text" id="contact_name" name="contact_name" class="form-control" maxlength="100" placeholder="Nom du contact">
                <label for="contact_name">Nom du contact</label>
              </div>
              <div class="form-floating">
                <input type="text" id="contact_phone" name="contact_phone" class="form-control" maxlength="20" placeholder="Téléphone">
                <label for="contact_phone">Numéro de téléphone</label>
              </div>
              <div class="form-floating job-form-field-wide">
                <input type="email" id="contact_mail" name="contact_mail" class="form-control" maxlength="100" placeholder="Adresse e-mail">
                <label for="contact_mail">E-mail</label>
              </div>
            </div>
          </section>

          <section class="job-form-section" aria-labelledby="jobTrackingSection">
            <h6 class="job-form-section-title" id="jobTrackingSection">
              <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
              Suivi de la candidature
            </h6>
            <div class="job-form-grid">
              <div class="form-floating">
                <input type="date" id="date_applied" name="date_applied" class="form-control" placeholder="Date de candidature">
                <label for="date_applied">Date de candidature</label>
              </div>
              <div class="form-floating">
                <input type="date" id="date_relance" name="date_relance" class="form-control" placeholder="Date de relance">
                <label for="date_relance">Date de relance</label>
              </div>
              <div class="form-floating job-form-field-centered">
                <select id="type_candidature" name="type_candidature" class="form-select">
                  <option value="">Non renseigné</option>
                  <option value="SPONTANEE">Spontanée</option>
                  <option value="ANNONCE">Annonce</option>
                </select>
                <label for="type_candidature">Type de candidature</label>
              </div>
            </div>
          </section>

          <section class="job-form-section" aria-labelledby="jobLinksSection">
            <h6 class="job-form-section-title" id="jobLinksSection">
              <i class="fa-solid fa-link" aria-hidden="true"></i>
              Liens utiles
            </h6>
            <div class="job-form-grid">
              <div class="form-floating job-form-field-wide">
                <input type="url" id="link_annonce" name="link_annonce" class="form-control" maxlength="2048" placeholder="Lien de l'annonce">
                <label for="link_annonce">Lien de l'annonce</label>
              </div>
              <div class="form-floating">
                <input type="url" id="link_linkedin" name="link_linkedin" class="form-control" maxlength="2048" placeholder="Lien LinkedIn">
                <label for="link_linkedin">Lien LinkedIn</label>
              </div>
              <div class="form-floating">
                <input type="url" id="company_website" name="company_website" class="form-control" maxlength="2048" placeholder="Site web">
                <label for="company_website">Site de l'entreprise</label>
              </div>
            </div>
          </section>

          <section class="job-form-section" aria-labelledby="jobNotesSection">
            <h6 class="job-form-section-title" id="jobNotesSection">
              <i class="fa-regular fa-note-sticky" aria-hidden="true"></i>
              Notes
            </h6>
            <div class="form-floating">
              <textarea id="notes_perso" name="notes_perso" class="form-control job-notes" rows="4" maxlength="5000" placeholder="Vos notes..."></textarea>
              <label for="notes_perso">Notes personnelles</label>
            </div>
          </section>
        </form>
      </div>

      <div class="modal-footer job-modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-job-modal-cancel>Annuler</button>
        <button type="submit" form="jobForm" id="saveBtn" class="btn btn-primary fw-bold is-hidden">Enregistrer</button>
      </div>
    </div>
  </div>
</div>
