# Picard API

API Symfony / API Platform utilisée par l'application Picard. Elle gère les produits, les utilisateurs, l'authentification JWT, la fidélité et les commandes.

## Liens du rendu

- Dépôt GitHub : <https://github.com/DevinciAlex/picard-api>
- Documentation API publique : <https://picard-api.onrender.com/api>
- Endpoint de contrôle : <https://picard-api.onrender.com/api/products>
- Vidéo de démonstration des routes : <https://youtu.be/0SF0GtuXnoI>

## Développement local

Prérequis : PHP, Composer et les extensions SQLite.

```bash
composer install
php bin/console doctrine:migrations:migrate
symfony server:start
```

L'API est alors disponible sur `http://localhost:8000/api`.

Les routes principales, le schéma Doctrine et les dépendances ont été contrôlés le 31 août 2026. Le compte rendu se trouve dans `preuves/RESULTATS-TESTS.md`.

## Déploiement Render

Le dépôt contient un `Dockerfile` et un `render.yaml`. Le service utilise PostgreSQL sur Render et exécute automatiquement les migrations au démarrage.

La variable `CORS_ALLOW_ORIGIN` doit autoriser l'URL publique du frontend. Les secrets et les clés JWT sont générés sur Render et ne sont pas enregistrés dans Git.

## Ressources et documentation

- Symfony : <https://symfony.com/doc/current/index.html>
- API Platform : <https://api-platform.com/docs/>
- API Platform avec Symfony : <https://api-platform.com/docs/symfony/>
- Opérations exposées par API Platform : <https://api-platform.com/docs/core/operations/>
- Doctrine ORM : <https://www.doctrine-project.org/projects/doctrine-orm/en/current/index.html>
- Doctrine dans Symfony : <https://symfony.com/doc/current/doctrine.html>
- Migrations Doctrine dans Symfony : <https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html>
- Authentification JWT avec API Platform : <https://api-platform.com/docs/core/jwt/>
- Bundle LexikJWTAuthentication : <https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html>
- Format JSON-LD/Hydra : <https://api-platform.com/docs/core/serialization/>
