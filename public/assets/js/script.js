$(document).ready(function() {
  /* ===============================
     1) Dark Mode
  =============================== */
  let darkMode = (localStorage.getItem('darkMode') === 'true');
  // Appliquer l'état aux toggles (desktop et mobile)
  $('#theme').prop('checked', darkMode);
  $('#themeMobile').prop('checked', darkMode);
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
      localStorage.setItem('darkMode', isDark);
  }
  $('#theme, #themeMobile').on('change', function() {
      let isDark = $(this).is(':checked');
      // Synchroniser les deux toggles
      $('#theme, #themeMobile').prop('checked', isDark);
      applyDarkMode(isDark);
  });

  /* ===============================
     2) Mise à jour de l'année dans le footer
  =============================== */
  const yearSpan = document.getElementById("currentYear");
  if (yearSpan) {
      yearSpan.textContent = new Date().getFullYear();
  }

  /* ===============================
     3) Confirmations globales
  =============================== */
  // Gestion des actions destructrices via .confirm-action
  $('.confirm-action').on('click', function(e) {
      e.preventDefault();
      var $this = $(this);
      var callback = $this.data('callback'); // Pour actions personnalisées
      var href = $this.data('href');         // Pour redirection
      // Pour le bouton de suppression de photo, on exécute directement sans confirmation
      if ($this.attr('id') === 'deletePhotoBtn') {
          if (callback && typeof window[callback] === "function") {
              window[callback]();
          }
          return;
      }
      var message = $this.data('confirm');
      if (confirm(message)) {
          if (callback && typeof window[callback] === "function") {
              window[callback]();
          } else if (href) {
              window.location.href = href;
          }
      }
  });

  // Callback pour supprimer la photo de profil
  window.deleteProfilePhoto = function() {
      $('#deletePhoto').prop('checked', true);
      $('#photoPreview').attr('src', '/assets/images/default-profile.png');
      $('#deletePhotoBtn').hide();
  };

  // Callback pour réinitialiser toutes les candidatures (job reset) via AJAX
  window.resetAllJobs = function() {
      $('.draggable-item').each(function(){
          let jobId = $(this).data('id');
          $.ajax({
              url: '/index.php?route=jobs&action=delete&id=' + jobId,
              type: 'GET',
              success: function(){
                  $('.draggable-item[data-id="'+jobId+'"]').remove();
              }
          });
      });
  };

  /* ===============================
     4) Prévisualisation de la photo de profil
  =============================== */
  $('#profilePic').on('change', function(e) {
      const file = e.target.files[0];
      const $preview = $('#photoPreview');
      if (!file) {
          $preview.attr('src', '/assets/images/default-profile.png');
          return;
      }
      if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(ev) {
              $preview.attr('src', ev.target.result);
          };
          reader.readAsDataURL(file);
      } else {
          $preview.attr('src', '/assets/images/default-profile.png');
      }
  });

  // Fermer le menu hamburger lorsqu'on clique sur un élément de la page
  document.addEventListener('click', function(event) {
    var isClickInside = document.getElementById('navbarNav').contains(event.target);
    var isToggleClicked = event.target.matches('.navbar-toggler');

    if (!isClickInside && !isToggleClicked) {
      var navbarCollapse = document.getElementById('navbarNav');
      var navbarToggler = document.querySelector('.navbar-toggler');

      if (navbarCollapse.classList.contains('show')) {
        navbarCollapse.classList.remove('show');
        navbarToggler.setAttribute('aria-expanded', 'false');
      }
    }
  });
});