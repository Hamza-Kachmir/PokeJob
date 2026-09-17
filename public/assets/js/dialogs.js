(function() {
    const dialog = document.getElementById('pokejobDialog');
    if (!dialog) return;

    const title = dialog.querySelector('[data-dialog-title]');
    const message = dialog.querySelector('[data-dialog-message]');
    const icon = dialog.querySelector('[data-dialog-icon]');
    const closeButton = dialog.querySelector('[data-dialog-close]');
    const cancelButton = dialog.querySelector('[data-dialog-cancel]');
    const confirmButton = dialog.querySelector('[data-dialog-confirm]');

    // La file empêche deux demandes de confirmation de se superposer
    let queue = Promise.resolve();

    const open = function(options) {
        return new Promise(function(resolve) {
            let settled = false;
            const controller = new AbortController();
            const finish = function(result) {
                if (settled) return;
                settled = true;
                controller.abort();
                if (dialog.open) dialog.close();
                resolve(result);
            };

            title.textContent = options.title;
            message.textContent = options.message;
            cancelButton.textContent = options.cancelLabel;
            confirmButton.textContent = options.confirmLabel;
            cancelButton.hidden = !options.showCancel;
            closeButton.hidden = options.hideClose === true;
            icon.className = 'pokejob-dialog-icon ' + options.variant;
            icon.innerHTML = options.variant === 'danger'
                ? '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>'
                : options.variant === 'warning'
                    ? '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>'
                    : '<i class="fa-solid fa-circle-info" aria-hidden="true"></i>';
            confirmButton.classList.toggle('btn-danger', options.variant === 'danger');
            confirmButton.classList.toggle('btn-primary', options.variant !== 'danger');

            const cancel = function() { finish(false); };
            const confirm = function() { finish(true); };
            closeButton.addEventListener('click', cancel, { signal: controller.signal });
            cancelButton.addEventListener('click', cancel, { signal: controller.signal });
            confirmButton.addEventListener('click', confirm, { signal: controller.signal });
            dialog.addEventListener('cancel', function(event) {
                event.preventDefault();
                cancel();
            }, { signal: controller.signal });
            dialog.showModal();
            if (options.focusConfirm) {
                window.setTimeout(function() { confirmButton.focus(); }, 80);
            }
        });
    };

    const schedule = function(options) {
        const result = queue.then(function() { return open(options); });
        queue = result.catch(function() { return false; });
        return result;
    };

    window.PokeJobDialog = {
        alert: function(messageText, options) {
            const settings = options || {};
            return schedule({
                title: settings.title || 'Information',
                message: messageText,
                confirmLabel: settings.confirmLabel || 'Fermer',
                cancelLabel: '',
                showCancel: false,
                hideClose: false,
                variant: settings.variant || 'info',
                focusConfirm: true
            });
        },
        confirm: function(messageText, options) {
            const settings = options || {};
            return schedule({
                title: settings.title || 'Confirmer l’action',
                message: messageText,
                confirmLabel: settings.confirmLabel || 'Confirmer',
                cancelLabel: settings.cancelLabel || 'Annuler',
                showCancel: true,
                hideClose: false,
                variant: settings.variant || 'warning',
                focusConfirm: settings.focusConfirm !== false
            });
        }
    };
})();
