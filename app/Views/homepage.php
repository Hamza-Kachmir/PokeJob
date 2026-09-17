<main class="container page-shell">
  <div class="mb-4 bg-light rounded-3 shadow-sm home-hero">
    <div class="container-fluid">
      <div class="hero-overview">
        <div class="hero-copy">
          <span class="section-kicker">Votre recherche enfin organisée</span>
          <h1 class="hero-title">
            <img src="<?= htmlspecialchars(App\Support\Asset::url('/assets/images/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="PokéJob" class="home-logo">
            <span>c’est quoi&nbsp;?</span>
          </h1>
          <p class="hero-subtitle">Une plateforme de gestion de candidatures</p>
          <p class="hero-description">Elle vous permet de gérer vos candidatures facilement, à chaque étape de votre recherche d’emploi.</p>

          <?php if (isset($_SESSION["user_id"])): ?>
            <p class="hero-storage-note">Vous êtes actuellement connecté. Vous pouvez profiter pleinement de PokéJob et retrouver vos candidatures sur tous vos appareils.</p>
          <?php else: ?>
            <p class="hero-storage-note">Commencez immédiatement, même sans compte. Vos candidatures restent enregistrées dans votre navigateur jusqu’au moment où vous décidez de les synchroniser en vous inscrivant.</p>
          <?php endif; ?>
          <div class="hero-actions">
            <?php if (isset($_SESSION["user_id"])): ?>
              <a href="/dashboard" class="btn btn-primary btn-lg">Mon dashboard</a>
              <a href="/account" class="btn btn-secondary btn-lg">Mon compte</a>
            <?php else: ?>
              <a href="/dashboard" class="btn btn-primary btn-lg">Essayer sans compte</a>
              <a href="/register" class="btn btn-secondary btn-lg">Créer un compte</a>
              <a href="/login" class="hero-login-link">J’ai déjà un compte</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <section class="storage-explainer" aria-labelledby="storage-title">
      <div class="storage-explainer-heading">
        <span class="section-kicker">À votre rythme</span>
      </div>
      <div class="storage-options">
        <article class="storage-option<?= !isset($_SESSION["user_id"]) ? " storage-option-highlighted" : "" ?>">
          <span class="storage-option-icon">
            <i class="fa-solid fa-laptop" aria-hidden="true"></i>
          </span>
          <div>
            <h3>Sans compte</h3>
            <p>Vos données sont conservées localement sur ce navigateur. Vous pouvez tester toutes les fonctions du dashboard immédiatement.</p>
          </div>
        </article>
        <article class="storage-option<?= isset($_SESSION["user_id"]) ? " storage-option-highlighted" : "" ?>">
          <span class="storage-option-icon">
            <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
          </span>
          <div>
            <h3>Avec un compte</h3>
            <p>Après votre inscription et votre connexion, vous choisissez si vous souhaitez transférer vos candidatures locales vers votre compte pour les retrouver sur tous vos appareils.</p>
          </div>
        </article>
      </div>
    </section>
  </div>
</main>
