<p align="center">
  <img src="public/assets/images/logo.png" alt="Logo PokéJob" width="190">
</p>

# PokéJob

<p align="center">
  <a href="https://github.com/Hamza-Kachmir/PokeJob/actions/workflows/ci.yml">
    <img src="https://github.com/Hamza-Kachmir/PokeJob/actions/workflows/ci.yml/badge.svg" alt="État des contrôles qualité">
  </a>
</p>

PokéJob est un projet que j’ai développé pour simplifier le suivi d’une recherche d’emploi.

L’idée est de regrouper ses candidatures dans un dashboard et de les faire avancer selon leur état : offre repérée, candidature envoyée, relance, entretien ou refus. Le site peut également être utilisé sans compte. Dans ce cas, les candidatures restent enregistrées dans le navigateur et leur import est proposé après la connexion à un compte.

➡️ **[Accéder à l’application PokéJob](https://pokejob.free.je/)**

## Fonctionnalités principales

- Gestion des candidatures dans un dashboard en cinq étapes.
- Ajout, modification, déplacement et suppression d’une candidature.
- Recherche par nom d’entreprise.
- Utilisation sans compte avec sauvegarde locale.
- Import des candidatures locales et détection des doublons.
- Inscription, connexion et option « rester connecté ».
- Vérification de l’adresse e-mail et récupération du mot de passe par code.
- Gestion du compte, export des candidatures et suppression des données.

## Fonctionnement

1. L’utilisateur ajoute, modifie et classe ses candidatures dans le dashboard.
2. Sans compte, les candidatures sont conservées localement dans le navigateur.
3. Après la création d’un compte et la connexion, l’utilisateur peut importer ses candidatures locales pour les retrouver sur ses appareils.
4. Chaque candidature peut évoluer au fil des étapes de recherche : je postule, j’ai postulé, je relance, j’ai un entretien ou refusé.

## Architecture technique

- **Frontend :** HTML, CSS, Bootstrap, JavaScript, jQuery et jQuery UI
- **Backend :** PHP 8.2 orienté objet avec une architecture MVC
- **Données :** MySQL 8
- **E-mails :** SMTP Gmail
- **Hébergement :** InfinityFree
- **Conteneurisation :** Docker et Docker Compose pour l'environnement local
- **Tests & qualité du code :** PHPUnit
- **Intégration continue :** GitHub Actions

## Améliorations prévues

- L'ajout d'une démo du dashboard pour présenter son fonctionnement.
- L'ajout de davantage de tests unitaires sur les principaux parcours utilisateur.
- La création d'un espace administrateur avec des statistiques sur les utilisateurs et les candidatures enregistrées.
- L'ajout des rappels de relance à partir d’une date choisie.
