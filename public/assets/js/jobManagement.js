$(document).ready(function() {
    const fields = [
        'company_name', 'contact_name', 'contact_phone', 'contact_mail',
        'link_annonce', 'link_linkedin', 'date_applied', 'date_relance',
        'notes_perso', 'company_website', 'type_candidature'
    ];

    let isDragging = false;

    // Fonction pour normaliser le texte (supprime les accents et met en minuscule)
    function normalizeText(text) {
        return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }

    // Initialisation du tri par glisser-déposer sur les éléments de candidature
    $('.connectedSortable').sortable({
        connectWith: '.connectedSortable',
        items: '.draggable-item',
        revert: true,
        distance: 10,
        start: function(event, ui) {
            isDragging = true;
            // Masque le texte "Voir les détails" lors du drag
            ui.item.find('.detail-text').hide();
        },
        stop: function(event, ui) {
            setTimeout(() => { isDragging = false; }, 300);
            let $item = ui.item;
            // Réaffiche le texte "Voir les détails" après le drag
            $item.find('.detail-text').show();
            let jobId = $item.data('id');
            let newStatus = $item.closest('.connectedSortable').data('status');

            // Mise à jour du statut via AJAX
            $.ajax({
                url: '/index.php?route=jobs&action=update_status',
                type: 'POST',
                data: { job_id: jobId, status: newStatus },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        console.log('Statut mis à jour');
                    } else {
                        console.error('Erreur lors de la mise à jour du statut', resp.error);
                    }
                },
                error: function() {
                    console.error('Erreur Ajax lors de la mise à jour du statut');
                }
            });

            // Réinitialise le champ de recherche, retire le focus et réaffiche tous les éléments
            $('#searchCompany').val('').blur();
            $('.draggable-item').show();
        }
    }).disableSelection();

    // Filtrage des candidatures par nom d'entreprise et gestion du focus :
    // Si le texte se supprime, on enlève le focus de la barre de recherche.
    $('#searchCompany').on('input', function(){
        var searchTerm = normalizeText($(this).val());
        if (searchTerm === "") {
            $(this).blur();
        }
        $('.draggable-item').each(function(){
            var companyName = normalizeText($(this).find('.company-name').text());
            if(companyName.indexOf(searchTerm) > -1){
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Réinitialise la recherche lors de la perte du focus
    $('#searchCompany').on('blur', function(){
        $(this).val('');
        $('.draggable-item').show();
    });

    // Gestion de l'ouverture de la modale pour ajouter ou modifier une candidature
    $('#jobModal').on('show.bs.modal', function(e) {
        if (isDragging) {
            e.preventDefault();
            return;
        }

        let button = e.relatedTarget;
        if (!button) return;
        let mode = $(button).data('mode') || 'new';
        let statusVal = $(button).data('status') || 'JE_POSTULE';
        let jobId = $(button).data('id') || '';

        $('#modeField').val(mode);
        $('#jobIdField').val(jobId);
        $('#statusField').val(statusVal);

        if (mode === 'new') {
            $('#jobModalLabel').text('Nouvelle Candidature');
            $('#jobForm').attr('action', '/index.php?route=jobs&action=store');
            fields.forEach(field => {
                $('#' + field).val('').prop('disabled', false).data('original', '');
            });
            $('#saveBtn').hide();
            $('#jobForm input, #jobForm textarea, #jobForm select')
              .off('input change')
              .on('input change', function() {
                  let original = $(this).data('original');
                  if ($(this).val() !== original) {
                      $('#saveBtn').show();
                  }
              });
        } else {
            $('#jobModalLabel').text('Détails de la Candidature');
            $('#jobForm').attr('action', '/index.php?route=jobs&action=update&id=' + jobId);
            $('#saveBtn').hide();
            $('#jobForm input, #jobForm textarea, #jobForm select')
              .off('input change')
              .on('input change', function() {
                  let original = $(this).data('original');
                  if ($(this).val() !== original) {
                      $('#saveBtn').show();
                  }
              });

            // Récupère les détails de la candidature via AJAX
            $.ajax({
                url: '/index.php?route=jobs&action=show&id=' + jobId,
                type: 'GET',
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        let job = resp.data;
                        fields.forEach(field => {
                            let value = job[field] || '';
                            if ((field === 'date_applied' || field === 'date_relance') && value === "0000-00-00") {
                                value = "";
                            }
                            $('#' + field).val(value).prop('disabled', false).data('original', value);
                        });
                        $('#statusField').val(job.status || 'JE_POSTULE');
                        $('#saveBtn').hide();
                    } else {
                        alert('Candidature introuvable');
                        $('#jobModal').modal('hide');
                    }
                },
                error: function() {
                    alert('Erreur Ajax');
                    $('#jobModal').modal('hide');
                }
            });
        }
    });
});
