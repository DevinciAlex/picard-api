# Picard API

API Symfony / API Platform utilisée par l'application Picard. Elle gère les produits, les utilisateurs, l'authentification JWT, la fidélité et les commandes.

## Développement local

Prérequis : PHP, Composer et les extensions SQLite.

```bash
composer install
php bin/console doctrine:migrations:migrate
symfony server:start
```

L'API est alors disponible sur `http://localhost:8000/api`.

## Déploiement Render

Le dépôt contient un `Dockerfile` et un `render.yaml`. Le service utilise PostgreSQL sur Render et exécute automatiquement les migrations au démarrage.

La variable `CORS_ALLOW_ORIGIN` doit autoriser l'URL publique du frontend. Les secrets et les clés JWT sont générés sur Render et ne sont pas enregistrés dans Git.
