# Mise en production de PokéJob

Le dossier public du serveur doit pointer vers `public`. Si InfinityFree impose `htdocs`, copiez le contenu de `public` dans `htdocs` et placez `app`, `vendor` et les autres dossiers hors de `htdocs` quand l’hébergeur le permet. Sinon, conservez les fichiers `.htaccess` de protection.

Créez un `.env` de production qui n’est jamais envoyé sur GitHub. Définissez `APP_ENV=production`, `APP_TIMEZONE=UTC`, un mot de passe MySQL unique, une longue valeur aléatoire pour `AUTH_CODE_PEPPER` et les identifiants SMTP de votre fournisseur. En production, l’application refuse désormais les valeurs de développement pour les secrets critiques. InfinityFree n’assure pas l’envoi SMTP PHP direct : utilisez un fournisseur SMTP externe.

Pour une installation neuve, créez une base vide puis importez `initdb/schema.sql` depuis phpMyAdmin. Ce fichier contient directement la structure complète attendue par l’application. Avant chaque déploiement, installez les dépendances de production avec `composer install --no-dev --optimize-autoloader`, puis effectuez les vérifications prévues par votre procédure de livraison.

Si l’application est placée derrière un reverse proxy, ajoutez uniquement ses adresses IP à `TRUSTED_PROXIES`, séparées par des virgules. Ne renseignez jamais cette variable avec une adresse générique ou une valeur provenant du client.

Le nettoyage des données temporaires et des anciens messages de contact est déclenché automatiquement par l’application, au maximum une fois par jour. Aucune tâche planifiée n’est nécessaire.
