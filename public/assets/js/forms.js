$(document).ready(function() {
    document.querySelectorAll('.modal[data-bs-backdrop="static"]').forEach(function(modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
    });

    // Validation interactive des mots de passe
    document.querySelectorAll('[data-password-policy]').forEach(function(input) {
        const form = input.closest('form');
        const checklist = form ? form.querySelector('[data-password-checklist]') : null;
        const confirmation = form ? form.querySelector('[data-password-confirm="' + input.id + '"]') : null;
        const matchFeedback = form ? form.querySelector('[data-password-match]') : null;
        const submitButton = form ? form.querySelector('[data-password-submit]') : null;
        if (!checklist) return;
        const sync = function() {
            const value = input.value;
            const rules = {
                length: value.length >= 8,
                uppercase: /[A-Z]/.test(value),
                digit: /\d/.test(value),
                special: /[^\p{L}\p{N}\s]/u.test(value)
            };
            Object.keys(rules).forEach(function(rule) {
                const item = checklist.querySelector('[data-rule="' + rule + '"]');
                if (item) item.classList.toggle('is-valid', rules[rule]);
            });
            const matches = confirmation && confirmation.value !== '' && confirmation.value === value;
            if (confirmation) {
                confirmation.setCustomValidity(confirmation.value === '' || matches ? '' : 'Les mots de passe ne correspondent pas.');
            }
            if (matchFeedback) {
                matchFeedback.classList.toggle('is-valid', Boolean(matches));
                matchFeedback.classList.toggle('is-invalid', Boolean(confirmation && confirmation.value !== '' && !matches));
                matchFeedback.textContent = confirmation && confirmation.value === ''
                    ? 'Confirmez votre mot de passe.'
                    : matches
                        ? 'Les mots de passe correspondent.'
                        : 'Les mots de passe ne correspondent pas.';
            }
            if (submitButton) {
                submitButton.disabled = !Object.values(rules).every(Boolean) || !matches;
            }
        };
        input.addEventListener('input', sync);
        if (confirmation) confirmation.addEventListener('input', sync);
        sync();
        window.setTimeout(sync, 300);
    });

    document.querySelectorAll('input[type="url"]').forEach(function(input) {
        const validateScheme = function() {
            const value = input.value.trim();
            input.setCustomValidity(value === '' || /^https?:\/\//i.test(value) ? '' : 'Utilisez une adresse commençant par http:// ou https://.');
        };
        input.addEventListener('input', validateScheme);
        validateScheme();
    });

    // Vérification de la disponibilité des adresses e-mail
    document.querySelectorAll('[data-email-availability]').forEach(function(input) {
        const form = input.closest('form');
        const feedback = form ? form.querySelector('[data-email-feedback]') : null;
        if (!form || !feedback) return;
        let timer = 0;
        let requestNumber = 0;

        const display = function(text, state) {
            feedback.textContent = text;
            feedback.classList.toggle('is-valid', state === 'valid');
            feedback.classList.toggle('is-invalid', state === 'invalid');
            feedback.classList.toggle('is-pending', state === 'pending');
        };

        input.addEventListener('input', function() {
            window.clearTimeout(timer);
            requestNumber++;
            input.setCustomValidity('');
            form.dataset.emailAvailable = '';
            if (input.value === '' || !input.checkValidity()) {
                display('', '');
                return;
            }

            const currentRequest = requestNumber;
            display('Vérification de l’adresse e-mail…', 'pending');
            timer = window.setTimeout(function() {
                fetch('/register?action=check_email', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({
                        csrf_token: document.querySelector('meta[name="csrf-token"]').content,
                        email: input.value
                    })
                }).then(function(response) {
                    if (!response.ok) throw new Error('verification_failed');
                    return response.json();
                }).then(function(result) {
                    if (currentRequest !== requestNumber) return;
                    if (result.available) {
                        form.dataset.emailAvailable = 'true';
                        input.setCustomValidity('');
                        display('Cette adresse e-mail est disponible.', 'valid');
                    } else {
                        form.dataset.emailAvailable = 'false';
                        input.setCustomValidity('Cette adresse e-mail est déjà utilisée.');
                        display('Cette adresse e-mail est déjà utilisée.', 'invalid');
                    }
                }).catch(function() {
                    if (currentRequest !== requestNumber) return;
                    form.dataset.emailAvailable = '';
                    input.setCustomValidity('');
                    display('La disponibilité sera vérifiée lors de l’inscription.', '');
                });
            }, 450);
        });

        form.addEventListener('submit', function(event) {
            if (form.dataset.emailAvailable === 'false') {
                event.preventDefault();
                input.reportValidity();
            }
        });

        if (input.value.trim() !== '') {
            input.dispatchEvent(new Event('input'));
        }
    });

    // Formulaires sensibles du compte
    if (document.getElementById('openEmailModal') && document.getElementById('emailModal')) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('emailModal')).show();
    }

    if (document.getElementById('openDeleteAccountModal') && document.getElementById('deleteAccountModal')) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteAccountModal')).show();
    }

    const accountEmailControl = document.getElementById('account_email');
    const currentAccountEmail = document.getElementById('current_account_email');
    const accountEmailButton = document.getElementById('accountEmailButton');
    const accountEmailModal = document.getElementById('emailModal');
    if (accountEmailControl && currentAccountEmail && accountEmailButton && accountEmailModal) {
        const feedback = document.querySelector('[data-account-email-feedback]');
        let available = false;
        let timer = 0;
        let requestNumber = 0;

        const displayAvailability = function(text, state) {
            if (!feedback) return;
            feedback.textContent = text;
            feedback.classList.toggle('is-valid', state === 'valid');
            feedback.classList.toggle('is-invalid', state === 'invalid');
            feedback.classList.toggle('is-pending', state === 'pending');
        };

        const updateAccountEmailButton = function() {
            const newEmail = accountEmailControl.value.trim().toLowerCase();
            const currentEmail = currentAccountEmail.value.trim().toLowerCase();
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(newEmail);
            accountEmailButton.disabled = !validEmail || newEmail === currentEmail || !available;
        };

        const unlockAccountEmailControl = function() {
            accountEmailControl.readOnly = false;
            accountEmailControl.removeEventListener('pointerdown', unlockAccountEmailControl);
            accountEmailControl.removeEventListener('focus', unlockAccountEmailControl);
            updateAccountEmailButton();
        };

        const checkAccountEmail = function() {
            window.clearTimeout(timer);
            requestNumber++;
            available = false;
            accountEmailControl.setCustomValidity('');
            const newEmail = accountEmailControl.value.trim().toLowerCase();
            const currentEmail = currentAccountEmail.value.trim().toLowerCase();
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(newEmail);
            updateAccountEmailButton();
            if (!newEmail || !validEmail) {
                displayAvailability('', '');
                return;
            }
            if (newEmail === currentEmail) {
                accountEmailControl.setCustomValidity('Cette adresse e-mail est déjà votre adresse actuelle.');
                displayAvailability('Cette adresse e-mail est déjà votre adresse actuelle.', 'invalid');
                return;
            }

            const currentRequest = requestNumber;
            displayAvailability('Vérification de l’adresse e-mail…', 'pending');
            timer = window.setTimeout(function() {
                fetch('/account?action=check_email', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: new URLSearchParams({
                        csrf_token: document.querySelector('meta[name="csrf-token"]').content,
                        email: accountEmailControl.value
                    })
                }).then(function(response) {
                    if (!response.ok) throw new Error('verification_failed');
                    return response.json();
                }).then(function(result) {
                    if (currentRequest !== requestNumber) return;
                    available = result.available === true;
                    accountEmailControl.setCustomValidity(available ? '' : 'Cette adresse e-mail est déjà utilisée.');
                    displayAvailability(
                        available ? 'Cette adresse e-mail est disponible.' : 'Cette adresse e-mail est déjà utilisée.',
                        available ? 'valid' : 'invalid'
                    );
                    updateAccountEmailButton();
                }).catch(function() {
                    if (currentRequest !== requestNumber) return;
                    available = false;
                    displayAvailability('Impossible de vérifier la disponibilité pour le moment.', 'invalid');
                    updateAccountEmailButton();
                });
            }, 450);
        };

        updateAccountEmailButton();
        accountEmailControl.addEventListener('pointerdown', unlockAccountEmailControl);
        accountEmailControl.addEventListener('focus', unlockAccountEmailControl);
        accountEmailControl.addEventListener('input', checkAccountEmail);
        accountEmailControl.addEventListener('keydown', function(event) {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            if (accountEmailButton.disabled) {
                accountEmailControl.reportValidity();
                return;
            }
            bootstrap.Modal.getOrCreateInstance(accountEmailModal).show();
        });
        if (accountEmailControl.value.trim() !== '') checkAccountEmail();
    }

    // Affichage et masquage des mots de passe
    document.querySelectorAll('input[type="password"]').forEach(function(input) {
        const field = input.closest('.form-floating');
        if (!field || field.querySelector('.password-visibility-toggle')) return;
        const button = document.createElement('button');
        const icon = document.createElement('i');
        button.type = 'button';
        button.className = 'password-visibility-toggle';
        button.setAttribute('aria-label', 'Afficher le mot de passe');
        button.setAttribute('aria-pressed', 'false');
        icon.className = 'fa-regular fa-eye';
        icon.setAttribute('aria-hidden', 'true');
        button.appendChild(icon);
        field.appendChild(button);
        field.classList.add('has-password-toggle');
        button.addEventListener('click', function() {
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            icon.className = reveal ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            button.setAttribute('aria-label', reveal ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            button.setAttribute('aria-pressed', String(reveal));
            input.focus({ preventScroll: true });
            if (typeof input.setSelectionRange === 'function') {
                const end = input.value.length;
                input.setSelectionRange(end, end);
            }
        });
    });

    // Contours animés communs aux champs de formulaire
    const floatingFields = document.querySelectorAll('.form-floating');

    floatingFields.forEach(function(field) {
        const control = field.querySelector('.form-control, .form-select');
        const label = field.querySelector('label');

        if (!control || !label || field.querySelector('.floating-notched-outline')) {
            return;
        }

        const outline = document.createElement('span');
        const leading = document.createElement('span');
        const notch = document.createElement('span');
        const trailing = document.createElement('span');
        const notchText = document.createElement('span');

        outline.className = 'floating-notched-outline';
        outline.setAttribute('aria-hidden', 'true');
        leading.className = 'floating-outline-leading';
        notch.className = 'floating-outline-notch';
        trailing.className = 'floating-outline-trailing';
        notchText.textContent = label.textContent.trim();
        notch.appendChild(notchText);
        outline.appendChild(leading);
        outline.appendChild(notch);
        outline.appendChild(trailing);
        field.insertBefore(outline, control);
        field.classList.add('has-notched-outline');

        const syncOutline = function() {
            const mustFloat = control.matches(':focus') ||
                control.tagName === 'SELECT' ||
                control.value !== '';
            field.classList.toggle('is-floating', mustFloat);
        };

        control.addEventListener('focus', syncOutline);
        control.addEventListener('blur', syncOutline);
        control.addEventListener('input', syncOutline);
        control.addEventListener('change', syncOutline);
        control.addEventListener('floatinglabelsync', syncOutline);
        syncOutline();
    });

    window.syncFloatingLabels = function(scope) {
        const root = scope || document;
        root.querySelectorAll('.form-floating > .form-control, .form-floating > .form-select').forEach(function(control) {
            control.dispatchEvent(new Event('floatinglabelsync'));
        });
    };

    [0, 100, 300, 700, 1500].forEach(function(delay) {
        window.setTimeout(function() {
            window.syncFloatingLabels(document);
        }, delay);
    });

    window.addEventListener('load', function() {
        window.syncFloatingLabels(document);
    });

    // Menus déroulants personnalisés
    function closeCustomSelect(container) {
        if (!container) return;
        container.classList.remove('custom-select-open');
        const trigger = container.querySelector('.custom-select-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    function closeAllCustomSelects(exception) {
        document.querySelectorAll('.has-custom-select.custom-select-open').forEach(function(container) {
            if (container !== exception) closeCustomSelect(container);
        });
    }

    let customSelectId = 0;
    window.enhanceCustomSelects = function(scope) {
        const root = scope || document;
        root.querySelectorAll('select.form-select, select.mobile-move-select').forEach(function(select) {
            if (select.dataset.customSelect === 'true') return;

            const container = select.parentElement;
            const trigger = document.createElement('button');
            const triggerText = document.createElement('span');
            const chevron = document.createElement('span');
            const menu = document.createElement('span');
            const instanceId = ++customSelectId;
            const label = Array.from(container.querySelectorAll('label')).find(function(candidate) {
                return candidate.htmlFor === select.id;
            });

            select.dataset.customSelect = 'true';
            select.classList.add('custom-select-native');
            select.tabIndex = -1;
            select.setAttribute('aria-hidden', 'true');
            container.classList.add('has-custom-select');
            trigger.type = 'button';
            trigger.className = 'custom-select-trigger';
            trigger.id = 'custom-select-trigger-' + instanceId;
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            if (label) {
                label.htmlFor = trigger.id;
            } else if (select.getAttribute('aria-label')) {
                trigger.setAttribute('aria-label', select.getAttribute('aria-label'));
            }
            triggerText.className = 'custom-select-trigger-text';
            chevron.className = 'custom-select-chevron';
            menu.className = 'custom-select-menu';
            menu.setAttribute('role', 'listbox');
            menu.id = 'custom-select-menu-' + instanceId;
            trigger.setAttribute('aria-controls', menu.id);

            const updateSelection = function() {
                const selectedOption = select.options[select.selectedIndex];
                triggerText.textContent = selectedOption ? selectedOption.textContent : '';
                trigger.disabled = select.disabled;
                menu.querySelectorAll('.custom-select-option').forEach(function(item) {
                    const selected = item.dataset.value === select.value;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
            };

            Array.from(select.options).forEach(function(option) {
                if (option.hidden || option.disabled) return;
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'custom-select-option';
                item.dataset.value = option.value;
                item.textContent = option.textContent;
                item.setAttribute('role', 'option');
                item.addEventListener('click', function() {
                    select.value = option.value;
                    updateSelection();
                    closeCustomSelect(container);
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    trigger.focus();
                });
                menu.appendChild(item);
            });

            trigger.appendChild(triggerText);
            trigger.appendChild(chevron);
            container.appendChild(trigger);
            container.appendChild(menu);
            updateSelection();

            trigger.addEventListener('click', function() {
                const opening = !container.classList.contains('custom-select-open');
                closeAllCustomSelects(container);
                container.classList.toggle('custom-select-open', opening);
                trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
            });

            trigger.addEventListener('keydown', function(event) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    container.classList.add('custom-select-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    const firstOption = menu.querySelector('.custom-select-option');
                    if (firstOption) firstOption.focus();
                }
                if (event.key === 'Escape') closeCustomSelect(container);
            });

            menu.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeCustomSelect(container);
                    trigger.focus();
                    return;
                }
                if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                    event.preventDefault();
                    const options = Array.from(menu.querySelectorAll('.custom-select-option'));
                    const currentIndex = options.indexOf(document.activeElement);
                    const nextIndex = event.key === 'Home'
                        ? 0
                        : event.key === 'End'
                            ? options.length - 1
                            : event.key === 'ArrowDown'
                                ? Math.min(options.length - 1, currentIndex + 1)
                                : Math.max(0, currentIndex - 1);
                    options[nextIndex]?.focus();
                }
            });

            container.addEventListener('focusout', function(event) {
                if (!container.contains(event.relatedTarget)) closeCustomSelect(container);
            });

            select.addEventListener('change', updateSelection);
            new MutationObserver(updateSelection).observe(select, { attributes: true, attributeFilter: ['disabled'] });
            select._updateCustomSelect = updateSelection;
        });
    };

    window.syncCustomSelects = function(scope) {
        const root = scope || document;
        root.querySelectorAll('select[data-custom-select="true"]').forEach(function(select) {
            if (typeof select._updateCustomSelect === 'function') select._updateCustomSelect();
        });
    };

    window.enhanceCustomSelects(document);

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.has-custom-select')) closeAllCustomSelects();
    });

});
