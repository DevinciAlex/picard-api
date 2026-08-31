# Résultats des tests de l'API

Date : 31 août 2026

## Contrôles techniques

- Mapping Doctrine : correct.
- Schéma SQLite : synchronisé avec les entités.
- Audit Composer : aucune vulnérabilité connue signalée.

## Routes testées

- Liste des produits : HTTP 200.
- Détail d'un produit : HTTP 200.
- Produit inexistant : HTTP 404.
- Modification puis restauration d'une note : HTTP 200.
- Création d'un panier : HTTP 201, statut initial `valid: false`.
- Validation du panier : HTTP 200, statut `valid: true`.
- Suppression d'un produit du panier : HTTP 200.
- Suppression du panier de test : HTTP 204.

## Correction réalisée pendant les tests

La propriété Doctrine du statut du panier avait une casse incohérente (`IsValid`), ce qui laissait la colonne `is_valid` à `NULL` et provoquait une erreur HTTP 500 à la création. La propriété et ses accesseurs utilisent désormais `isValid`; une nouvelle création et une validation de panier ont ensuite réussi.
