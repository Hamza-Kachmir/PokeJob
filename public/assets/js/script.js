$(document).ready(function() {
    const year = document.getElementById('currentYear');
    if (year) year.textContent = String(new Date().getFullYear());

    document.querySelectorAll('a.btn').forEach(function(linkButton) {
        linkButton.setAttribute('draggable', 'false');
    });

    document.addEventListener('dragstart', function(event) {
        if (event.target.closest('a.btn')) {
            event.preventDefault();
        }
    });

    // Synchronisation du thème avec la préférence enregistrée
    let darkMode = false;
    try {
        darkMode = localStorage.getItem('darkMode') === 'true';
    } catch (error) {
        darkMode = false;
    }
    $('#theme').prop('checked', darkMode);
    if (darkMode) {
        $('html').attr('dark-theme', 'dark');
    } else {
        $('html').removeAttr('dark-theme');
    }
    function applyDarkMode(isDark) {
        if (isDark) {
            $('html').attr('dark-theme', 'dark');
        } else {
            $('html').removeAttr('dark-theme');
        }
        try {
            localStorage.setItem('darkMode', isDark);
        } catch (error) {
            // Le thème reste appliqué pour la session même si le stockage est indisponible
        }
    }
    $('#theme').on('change', function() {
        const isDark = $(this).is(':checked');
        applyDarkMode(isDark);
    });

    // Sur desktop, le menu du compte s'ouvre dès que le pointeur atteint l'avatar ou sa flèche
    const profileMenu = document.querySelector('.profile-menu');
    const profileTrigger = profileMenu ? profileMenu.querySelector('.profile-menu-trigger') : null;
    const desktopProfileHover = window.matchMedia('(min-width: 992px) and (hover: hover) and (pointer: fine)');
    if (profileMenu && profileTrigger) {
        const profileDropdown = bootstrap.Dropdown.getOrCreateInstance(profileTrigger);
        let profileCloseTimer = 0;

        profileMenu.addEventListener('mouseenter', function() {
            if (!desktopProfileHover.matches) return;
            window.clearTimeout(profileCloseTimer);
            profileDropdown.show();
        });

        profileMenu.addEventListener('mouseleave', function() {
            if (!desktopProfileHover.matches) return;
            window.clearTimeout(profileCloseTimer);
            profileCloseTimer = window.setTimeout(function() {
                profileDropdown.hide();
                profileTrigger.blur();
            }, 150);
        });
    }

    // Confirmations génériques des actions destructives
    $('.confirm-action').on('click', async function(e) {
        e.preventDefault();
        const $this = $(this);
        const callback = $this.data('callback');
        const href = $this.data('href');

        const message = $this.data('confirm');
        const confirmed = await window.PokeJobDialog.confirm(message, {
            title: 'Confirmer la suppression',
            confirmLabel: 'Supprimer',
            cancelLabel: 'Annuler',
            variant: 'danger'
        });
        if (confirmed) {
            if (callback && typeof window[callback] === "function") {
                window[callback]();
            } else if (href) {
                window.location.href = href;
            }
        }
    });

    // Navigation mobile
    const mobileNavigation = document.getElementById('navbarNav');
    const navbarToggler = document.querySelector('.navbar-toggler');

    if (mobileNavigation && navbarToggler) {
        const animatedToggle = navbarToggler.querySelector('.navbar-animated');

        mobileNavigation.addEventListener('show.bs.collapse', function() {
            mobileNavigation.closest('.navbar')?.classList.add('menu-open');
            if (animatedToggle) animatedToggle.classList.add('open');
        });

        mobileNavigation.addEventListener('hide.bs.collapse', function() {
            if (animatedToggle) animatedToggle.classList.remove('open');
        });

        mobileNavigation.addEventListener('shown.bs.collapse', function() {
            navbarToggler.setAttribute('aria-label', 'Fermer le menu');
            if (animatedToggle) animatedToggle.classList.add('open');
        });

        mobileNavigation.addEventListener('hidden.bs.collapse', function() {
            mobileNavigation.closest('.navbar')?.classList.remove('menu-open');
            navbarToggler.setAttribute('aria-label', 'Ouvrir le menu');
            if (animatedToggle) animatedToggle.classList.remove('open');
        });
    }

    document.addEventListener('click', function(event) {
        const navElem = document.getElementById('navbarNav');
        if (!navElem) return;
        const isClickInside = navElem.contains(event.target);
        const isToggleClicked = Boolean(event.target.closest('.navbar-toggler'));

        if (!isClickInside && !isToggleClicked) {
            if (navElem.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(navElem, { toggle: false }).hide();
            }
        }
    });

});
