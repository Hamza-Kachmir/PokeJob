$(document).ready(function() {
    // Les visiteurs conservent leurs candidatures dans le navigateur jusqu’à leur connexion
    const STORAGE_KEY = 'pokejob_guest_jobs';
    const $dashboard = $('#dashboardApp');
    const isDashboard = $dashboard.length > 0;
    const isAuthenticated = !isDashboard || $dashboard.data('authenticated') === true;
    const maxJobs = Number($dashboard.attr('data-job-limit')) || 1000;
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
    const fields = [
        'job_title', 'company_name', 'contact_name', 'contact_phone', 'contact_mail',
        'link_annonce', 'link_linkedin', 'date_applied', 'date_relance',
        'notes_perso', 'company_website', 'type_candidature'
    ];

    let isDragging = false;
    let duplicateApproved = false;

    const statusTitles = {
        'JE_POSTULE': 'Je postule',
        'POSTULE': 'J’ai postulé',
        'RELANCE': 'Je relance',
        'ENTRETIEN': 'J’ai un entretien',
        'REFUSE': 'Refusé'
    };

    const statusAppearanceClasses = {
        'JE_POSTULE': 'status-je-postule',
        'POSTULE': 'status-postule',
        'RELANCE': 'status-relance',
        'ENTRETIEN': 'status-entretien',
        'REFUSE': 'status-refuse'
    };

    function setJobModalAppearance(status, mode) {
        const normalizedStatus = statusTitles[status] ? status : 'JE_POSTULE';
        const $modal = $('#jobModal');

        $modal.removeClass(Object.values(statusAppearanceClasses).join(' '));
        $modal.addClass(statusAppearanceClasses[normalizedStatus]);
        $('[data-job-modal-kicker]').text(mode === 'new' ? 'Nouvelle candidature' : statusTitles[normalizedStatus]);
        $('[data-job-modal-cancel]').text(mode === 'new' ? 'Annuler' : 'Fermer');
        $('#saveBtn').text(mode === 'new' ? 'Enregistrer' : 'Enregistrer les modifications');
    }

    function normalizeText(text) {
        return String(text || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }

    function filterJobsByCompany() {
        const searchTerm = normalizeText($('#searchCompany').val());
        $('.draggable-item').each(function() {
            const companyName = normalizeText($(this).find('.company-meta').text());
            $(this).toggleClass('is-hidden', companyName.indexOf(searchTerm) === -1);
        });
        $('#clearCompanySearch').toggleClass('is-visible', searchTerm !== '');
    }

    function clearCompanySearch() {
        $('#searchCompany').val('').trigger('blur');
        filterJobsByCompany();
        if (typeof window.syncFloatingLabels === 'function') {
            window.syncFloatingLabels(document.getElementById('dashboardApp'));
        }
    }

    function showError(message, title) {
        return window.PokeJobDialog.alert(message, {
            title: title || 'Une erreur est survenue',
            variant: 'danger'
        });
    }

    function showInfo(message, title) {
        return window.PokeJobDialog.alert(message, {
            title: title || 'Information',
            variant: 'info'
        });
    }

    function showJobLimit() {
        return showInfo(
            'Vous avez atteint la limite de ' + maxJobs + ' candidatures. Supprimez une candidature avant d’en ajouter une nouvelle.',
            'Limite atteinte'
        );
    }

    function confirmDeletion(message, title) {
        return window.PokeJobDialog.confirm(message, {
            title: title || 'Confirmer la suppression',
            confirmLabel: 'Supprimer',
            cancelLabel: 'Annuler',
            variant: 'danger',
            focusConfirm: false
        });
    }

    function confirmDuplicate(message, confirmLabel, cancelLabel) {
        return window.PokeJobDialog.confirm(message, {
            title: 'Doublon détecté',
            confirmLabel: confirmLabel || 'Ajouter quand même',
            cancelLabel: cancelLabel || 'Annuler',
            variant: 'warning'
        });
    }

    function getGuestJobs() {
        try {
            const jobs = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            return Array.isArray(jobs) ? jobs : [];
        } catch (error) {
            return [];
        }
    }

    function saveGuestJobs(jobs) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(jobs));
            return true;
        } catch (error) {
            showError("Impossible d’enregistrer vos candidatures dans ce navigateur.");
            return false;
        }
    }

    function createGuestId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return 'guest-' + window.crypto.randomUUID();
        }
        return 'guest-' + Date.now() + '-' + Math.random().toString(16).slice(2);
    }

    function buildJobCard(job) {
        const $card = $('<div>', {
            class: 'border mb-2 draggable-item',
            'data-id': job.id
        });
        const $label = $('<div>', { class: 'company-label' });
        const $title = $('<span>', {
            class: 'fw-bold company-name',
            text: job.job_title || 'Poste non renseigné'
        });
        const $delete = $('<button>', {
            type: 'button',
            class: 'delete-cross guest-delete',
            'data-id': job.id,
            'aria-label': 'Supprimer cette candidature'
        }).append($('<i>', {
            class: 'fa-solid fa-xmark',
            'aria-hidden': 'true'
        }));
        const $company = $('<div>', {
            class: 'text-muted small mb-1 px-2 company-meta',
            text: job.company_name || ''
        });
        const $details = $('<div>', { class: 'detail-container' }).append(
            $('<button>', {
                type: 'button',
                class: 'fw-bold detail-text',
                'data-id': job.id,
                'data-mode': 'detail',
                'data-bs-toggle': 'modal',
                'data-bs-target': '#jobModal',
                text: 'Voir les détails'
            })
        );

        return $card.append($label.append($title, $delete), $company, $details);
    }

    function buildMoveControl(currentStatus) {
        const $select = $('<select>', {
            class: 'mobile-move-select',
            'aria-label': 'Déplacer cette candidature'
        });

        $select.append($('<option>', {
            value: '',
            text: 'Déplacer',
            selected: true,
            disabled: true,
            hidden: true
        }));

        Object.keys(statusTitles).forEach(function(status) {
            if (status !== currentStatus) {
                $select.append($('<option>', {
                    value: status,
                    text: statusTitles[status]
                }));
            }
        });

        return $('<div>', { class: 'mobile-move-control' }).append($select);
    }

    function refreshMoveControls() {
        $('.draggable-item').each(function() {
            const $card = $(this);
            const currentStatus = String($card.closest('.connectedSortable').data('status') || 'JE_POSTULE');
            $card.find('.mobile-move-control').remove();
            $card.find('.detail-container').append(buildMoveControl(currentStatus));
        });
        if (typeof window.enhanceCustomSelects === 'function') {
            window.enhanceCustomSelects(document);
        }
    }

    function renderGuestJobs() {
        if (!isDashboard || isAuthenticated) return;

        $('.connectedSortable .draggable-item').remove();
        getGuestJobs().forEach(function(job) {
            const status = statusTitles[job.status] ? job.status : 'JE_POSTULE';
            job.status = status;
            $('.connectedSortable[data-status="' + status + '"]').append(buildJobCard(job));
        });
        refreshMoveControls();
    }

    function readFormJob() {
        const job = {};
        fields.forEach(function(field) {
            job[field] = String($('#' + field).val() || '').trim();
        });
        job.status = $('#statusField').val() || 'JE_POSTULE';
        return job;
    }

    function fillForm(job) {
        fields.forEach(function(field) {
            let value = job[field] || '';
            if ((field === 'date_applied' || field === 'date_relance') && value === '0000-00-00') {
                value = '';
            }
            $('#' + field).val(value).prop('disabled', false).data('original', value);
        });
        const status = job.status;
        $('#statusField').val(status || 'JE_POSTULE');
        setJobModalAppearance(status || 'JE_POSTULE', 'detail');
        if (typeof window.syncFloatingLabels === 'function') {
            window.syncFloatingLabels(document.getElementById('jobModal'));
        }
        if (typeof window.syncCustomSelects === 'function') {
            window.syncCustomSelects(document.getElementById('jobModal'));
        }
    }

    if (!isDashboard) return;

    renderGuestJobs();
    refreshMoveControls();

    const limitNoticeRequested = Boolean(document.getElementById('jobLimitReached'));
    if (limitNoticeRequested) {
        showJobLimit();
    }

    if (isAuthenticated) {
        const guestJobs = getGuestJobs();
        if (guestJobs.length && document.getElementById('guestImportModal')) {
            if ($('.connectedSortable .draggable-item').length >= maxJobs) {
                if (!limitNoticeRequested) {
                    showJobLimit();
                }
            } else {
                $('#guestImportMessage').text(guestJobs.length + ' candidature' + (guestJobs.length > 1 ? 's ont' : ' a') + ' été trouvée' + (guestJobs.length > 1 ? 's' : '') + ' dans ce navigateur. Voulez-vous les ajouter à votre compte ?');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('guestImportModal')).show();
            }
        }
    }

    $('#importGuestJobs').on('click', function() {
        const jobs = getGuestJobs();
        const $button = $(this).prop('disabled', true);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('guestImportModal')).hide();
        $.post('/dashboard?action=import_guest_jobs', {
            csrf_token: csrfToken,
            guest_jobs: JSON.stringify(jobs),
            preview: '1'
        }, null, 'json').done(async function(preview) {
            let includeDuplicates = false;
            if (preview.duplicates > 0) {
                includeDuplicates = await confirmDuplicate(
                    preview.duplicates + ' candidature' + (preview.duplicates > 1 ? 's identiques ont' : ' identique a') + ' été détectée' + (preview.duplicates > 1 ? 's' : '') + '. Voulez-vous aussi ' + (preview.duplicates > 1 ? 'les importer' : 'l’importer') + ' ?',
                    'Importer quand même',
                    'Ignorer les doublons'
                );
            }
            $.post('/dashboard?action=import_guest_jobs', {
                csrf_token: csrfToken,
                guest_jobs: JSON.stringify(jobs),
                include_duplicates: includeDuplicates ? '1' : '0'
            }, null, 'json').done(async function(result) {
                if (!result.success) {
                    showError('L’import a échoué. Vos données locales sont conservées.', 'Import impossible');
                    return;
                }
                const handledIndexes = new Set((result.handled_indexes || []).map(Number));
                const remainingJobs = jobs.filter(function(job, index) {
                    return !handledIndexes.has(index);
                });
                if (remainingJobs.length > 0) {
                    if (!saveGuestJobs(remainingJobs)) return;
                    if (result.limit_reached) {
                        await showJobLimit();
                    } else {
                        await showInfo(
                            result.invalid > 0
                                ? result.invalid + ' candidature(s) invalide(s) ont été conservée(s) dans ce navigateur. Les autres ont été traitées.'
                                : remainingJobs.length + ' candidature(s) restent à importer et sont conservées dans ce navigateur.',
                            'Import partiel'
                        );
                    }
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
                window.location.reload();
            }).fail(function() {
                showError('L’import a échoué. Vos données locales sont conservées.', 'Import impossible');
            }).always(function() {
                $button.prop('disabled', false);
            });
        }).fail(function() {
            showError('Impossible de vérifier les candidatures locales.', 'Vérification impossible');
            $button.prop('disabled', false);
        });
    });

    $('#discardGuestJobs').on('click', function() {
        localStorage.removeItem(STORAGE_KEY);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('guestImportModal')).hide();
    });

    window.resetAllJobs = function() {
        clearCompanySearch();
        if (!isAuthenticated) {
            if (saveGuestJobs([])) {
                $('.draggable-item').remove();
            }
            return;
        }

        const $button = $('#deleteAllJobs').prop('disabled', true);
        $.ajax({
            url: '/dashboard?action=delete_all',
            type: 'POST',
            dataType: 'json',
            data: { csrf_token: csrfToken }
        }).done(function(response) {
            if (response.success) {
                $('.draggable-item').remove();
                return;
            }
            showError('Impossible de supprimer toutes les candidatures.', 'Suppression impossible');
        }).fail(function() {
            showError('Impossible de supprimer toutes les candidatures.', 'Suppression impossible');
        }).always(function() {
            $button.prop('disabled', false);
        });
    };

    $(document).on('click', '.server-delete', async function(event) {
        event.preventDefault();
        const jobId = String($(this).data('id'));
        if (!await confirmDeletion('Voulez-vous vraiment supprimer cette candidature ?', 'Supprimer la candidature ?')) return;
        $.ajax({
            url: '/dashboard?action=delete',
            type: 'POST',
            dataType: 'json',
            data: { job_id: jobId, csrf_token: csrfToken }
        }).done(function(response) {
            if (response.success) {
                $('.draggable-item[data-id="' + jobId + '"]').remove();
                clearCompanySearch();
            }
        }).fail(function() {
            showError('Impossible de supprimer cette candidature.', 'Suppression impossible');
        });
    });

    $(document).on('click', '.guest-delete', async function(event) {
        event.preventDefault();
        const jobId = String($(this).data('id'));
        if (!await confirmDeletion('Voulez-vous vraiment supprimer cette candidature ?', 'Supprimer la candidature ?')) return;

        const jobs = getGuestJobs().filter(function(job) {
            return String(job.id) !== jobId;
        });
        if (saveGuestJobs(jobs)) {
            renderGuestJobs();
            clearCompanySearch();
        }
    });

    // Le glisser-déposer est réservé aux pointeurs précis ; le mobile utilise le menu de déplacement
    const supportsDesktopDrag = !window.matchMedia || window.matchMedia('(pointer: fine)').matches;

    if (supportsDesktopDrag) {
        let $dragSource = null;
        let $dragLayoutSpacer = null;
        let originalStatus = '';

        const preserveDragSourceLayout = function(ui) {
            if (!$dragSource || !$dragLayoutSpacer) return;

            if (ui.placeholder.parent()[0] === $dragSource[0]) {
                $dragLayoutSpacer.detach();
            } else if (!$dragLayoutSpacer.parent().length) {
                $dragSource.append($dragLayoutSpacer);
            }
        };

        $('.connectedSortable').sortable({
            connectWith: '.connectedSortable',
            items: '.draggable-item:not(.drag-layout-spacer)',
            revert: true,
            distance: 10,
            forceHelperSize: true,
            forcePlaceholderSize: true,
            placeholder: 'job-sortable-placeholder',
            start: function(event, ui) {
                isDragging = true;
                $dragSource = ui.item.parent();
                originalStatus = String($dragSource.data('status') || '');
                $dragLayoutSpacer = ui.item.clone(false)
                    .removeClass('ui-sortable-helper')
                    .addClass('drag-layout-spacer')
                    .removeAttr('data-id style')
                    .attr('aria-hidden', 'true');
                $dragLayoutSpacer.find('[id]').removeAttr('id');
                $dragLayoutSpacer.find('a, button, input, select, textarea').attr('tabindex', '-1');
            },
            sort: function(event, ui) {
                preserveDragSourceLayout(ui);
            },
            stop: function(event, ui) {
                setTimeout(function() { isDragging = false; }, 300);
                const $item = ui.item;
                const jobId = String($item.data('id'));
                const newStatus = $item.closest('.connectedSortable').data('status');
                const previousStatus = originalStatus;
                if ($dragLayoutSpacer) $dragLayoutSpacer.remove();
                $dragSource = null;
                $dragLayoutSpacer = null;

                if (String(newStatus) === previousStatus) {
                    refreshMoveControls();
                    clearCompanySearch();
                    return;
                }

                const $destination = $('.connectedSortable[data-status="' + newStatus + '"]');
                $destination.children('.new-text').first().after($item);

                if (!isAuthenticated) {
                    const jobs = getGuestJobs();
                    const jobIndex = jobs.findIndex(function(item) { return String(item.id) === jobId; });
                    const job = jobIndex >= 0 ? jobs[jobIndex] : null;
                    if (job) {
                        job.status = newStatus;
                        jobs.splice(jobIndex, 1);
                        jobs.unshift(job);
                        saveGuestJobs(jobs);
                    }
                } else {
                    $.ajax({
                        url: '/dashboard?action=update_status',
                        type: 'POST',
                        data: { job_id: jobId, status: newStatus, csrf_token: csrfToken },
                        dataType: 'json'
                    }).done(function(response) {
                        if (response.success) return;
                        $('.connectedSortable[data-status="' + previousStatus + '"]').append($item);
                        refreshMoveControls();
                        showError('Impossible de déplacer cette candidature.', 'Déplacement impossible');
                    }).fail(function() {
                        $('.connectedSortable[data-status="' + previousStatus + '"]').append($item);
                        refreshMoveControls();
                        showError('Impossible de déplacer cette candidature.', 'Déplacement impossible');
                    });
                }

                refreshMoveControls();
                clearCompanySearch();
            }
        }).disableSelection();
    }

    $(document).on('contextmenu', '.draggable-item', function(event) {
        if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) {
            event.preventDefault();
        }
    });

    $(document).on('change', '.mobile-move-select', function() {
        const $select = $(this);
        const newStatus = String($select.val() || '');
        const $card = $select.closest('.draggable-item');
        const jobId = String($card.data('id'));

        if (!statusTitles[newStatus]) return;

        if (!isAuthenticated) {
            const jobs = getGuestJobs();
            const jobIndex = jobs.findIndex(function(item) { return String(item.id) === jobId; });
            const job = jobIndex >= 0 ? jobs[jobIndex] : null;
            if (job) {
                job.status = newStatus;
                jobs.splice(jobIndex, 1);
                jobs.unshift(job);
                if (saveGuestJobs(jobs)) renderGuestJobs();
            }
            return;
        }

        $select.prop('disabled', true);
        $.ajax({
            url: '/dashboard?action=update_status',
            type: 'POST',
            data: { job_id: jobId, status: newStatus, csrf_token: csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const $destination = $('.connectedSortable[data-status="' + newStatus + '"]');
                    $destination.children('.new-text').first().after($card);
                    refreshMoveControls();
                    return;
                }
                showError('Impossible de déplacer cette candidature.', 'Déplacement impossible');
                $select.val('').prop('disabled', false);
            },
            error: function() {
                showError('Erreur lors du déplacement de la candidature.', 'Déplacement impossible');
                $select.val('').prop('disabled', false);
            }
        });
    });

    $('#searchCompany').on('input', filterJobsByCompany);

    $('#clearCompanySearch').on('click', function() {
        clearCompanySearch();
        this.blur();
    });

    $('#searchCompany').on('blur', function() {
        if ($(this).val() === '') {
            $('.draggable-item').removeClass('is-hidden');
        }
    });

    $('#jobModal').on('show.bs.modal', function(event) {
        if (isDragging) {
            event.preventDefault();
            return;
        }

        const button = event.relatedTarget;
        if (!button) return;

        const mode = $(button).data('mode') || 'new';
        const status = $(button).data('status') || $(button).closest('.connectedSortable').data('status') || 'JE_POSTULE';
        const jobId = String($(button).data('id') || '');

        const currentJobCount = isAuthenticated
            ? $('.connectedSortable .draggable-item').length
            : getGuestJobs().length;
        if (mode === 'new' && currentJobCount >= maxJobs) {
            event.preventDefault();
            showJobLimit();
            return;
        }

        $('#modeField').val(mode);
        $('#jobIdField').val(jobId);
        $('#statusField').val(status);
        setJobModalAppearance(status, mode);

        const $formFields = $('#jobForm input:not([type="hidden"]), #jobForm textarea, #jobForm select');
        const updateSaveButtonVisibility = function() {
            const hasChanges = $formFields.toArray().some(function(field) {
                return $(field).val() !== $(field).data('original');
            });
            $('#saveBtn').toggleClass('is-hidden', !hasChanges);
        };

        $formFields
            .off('.saveVisibility')
            .on('input.saveVisibility change.saveVisibility keyup.saveVisibility compositionend.saveVisibility focusout.saveVisibility', updateSaveButtonVisibility)
            .on('paste.saveVisibility cut.saveVisibility', function() {
                setTimeout(updateSaveButtonVisibility, 0);
            });

        if (mode === 'new') {
            $('#jobModalLabel').text(statusTitles[status] || 'Nouvelle candidature');
            $('#jobForm').attr('action', '/dashboard?action=store');
            fields.forEach(function(field) {
                $('#' + field).val('').prop('disabled', false).data('original', '');
            });
            if (typeof window.syncFloatingLabels === 'function') {
                window.syncFloatingLabels(document.getElementById('jobModal'));
            }
            if (typeof window.syncCustomSelects === 'function') {
                window.syncCustomSelects(document.getElementById('jobModal'));
            }
            $('#saveBtn').addClass('is-hidden');
            return;
        }

        $('#jobModalLabel').text('Détails de la candidature');
        $('#jobForm').attr('action', '/dashboard?action=update&id=' + encodeURIComponent(jobId));
        $('#saveBtn').addClass('is-hidden');

        if (!isAuthenticated) {
            const job = getGuestJobs().find(function(item) { return String(item.id) === jobId; });
            if (job) {
                fillForm(job);
            } else {
                showError('Cette candidature est introuvable.', 'Candidature introuvable');
                event.preventDefault();
            }
            return;
        }

        $.ajax({
            url: '/dashboard?action=show&id=' + encodeURIComponent(jobId),
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    fillForm(response.data);
                    $('#saveBtn').addClass('is-hidden');
                } else {
                    showError('Cette candidature est introuvable.', 'Candidature introuvable');
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('jobModal')).hide();
                }
            },
            error: function() {
                showError('La candidature n’a pas pu être chargée.', 'Chargement impossible');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('jobModal')).hide();
            }
        });
    });

    $('#jobForm').on('submit', async function(event) {
        const job = readFormJob();
        if (!job.company_name) {
            event.preventDefault();
            $('#company_name').trigger('focus');
            return;
        }

        const jobs = getGuestJobs();
        const mode = $('#modeField').val();
        const jobId = String($('#jobIdField').val() || '');
        const duplicateKeyChanged = mode === 'new' ||
            normalizeText(job.company_name) !== normalizeText($('#company_name').data('original')) ||
            normalizeText(job.job_title) !== normalizeText($('#job_title').data('original'));

        if (isAuthenticated) {
            if (duplicateApproved) {
                duplicateApproved = false;
                return;
            }
            if (!duplicateKeyChanged) {
                return;
            }
            event.preventDefault();
            $.post('/dashboard?action=check_duplicate', {
                csrf_token: csrfToken,
                company_name: job.company_name,
                job_title: job.job_title,
                job_id: jobId
            }, null, 'json').done(async function(result) {
                if (result.duplicate && !await confirmDuplicate('Cette candidature existe déjà pour ce poste et cette entreprise. Voulez-vous quand même l’ajouter ?')) {
                    return;
                }
                duplicateApproved = true;
                $('#allowDuplicateField').val(result.duplicate ? '1' : '0');
                document.getElementById('jobForm').requestSubmit();
            }).fail(function() {
                showError('La vérification du doublon a échoué. Réessayez.', 'Vérification impossible');
            });
            return;
        }

        event.preventDefault();

        if (mode === 'new' && jobs.length >= maxJobs) {
            showJobLimit();
            return;
        }

        const duplicate = duplicateKeyChanged && jobs.some(function(item) {
            return String(item.id) !== jobId &&
                normalizeText(item.company_name) === normalizeText(job.company_name) &&
                normalizeText(item.job_title) === normalizeText(job.job_title);
        });
        if (duplicate && !await confirmDuplicate('Cette candidature existe déjà pour ce poste et cette entreprise. Voulez-vous quand même l’ajouter ?')) return;

        if (mode === 'detail' && jobId) {
            const index = jobs.findIndex(function(item) { return String(item.id) === jobId; });
            if (index !== -1) {
                jobs[index] = $.extend({}, jobs[index], job, { id: jobs[index].id });
            }
        } else {
            job.id = createGuestId();
            job.created_at = new Date().toISOString();
            jobs.unshift(job);
        }

        if (saveGuestJobs(jobs)) {
            renderGuestJobs();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('jobModal')).hide();
        }
    });
});
