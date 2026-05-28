# PokéShop — Site de vente de cartes Pokémon à l'unité

**Projet PHP M1 — Option B : Site Web CRUD MVC**  
Formation UC D43 | Période : Février-Avril 2024 | Référente : Morgane Flamant

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Backend | PHP 8.5 + Symfony 7.x |
| Base de données principale | MongoDB 6.x (Doctrine ODM) |
| Templates | Twig |
| Frontend | Bootstrap 5.3 + JavaScript (Fetch/AJAX) |
| Tests | PHPUnit 11 |
| API externe | PokeAPI (cURL) |

---

## Installation

### Prérequis

- PHP 8.5+ avec extensions : `mongodb`, `curl`, `intl`, `mbstring`, `openssl`, `zip`
- Composer
- MongoDB 6.x
- Git

### 1. Cloner le projet

```bash
git clone https://github.com/votre-repo/pokemon-cards.git
cd pokemon-cards
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env .env.local
# Modifier .env.local :
# MONGODB_URI=mongodb://localhost:27017
# MONGODB_DB=pokemon_shop
```

### 4. Démarrer MongoDB

```bash
mongod --dbpath /data/db --port 27017
```

### 5. Créer les index MongoDB

```bash
mongosh pokemon_shop mongodb/01_indexes.js
```

### 6. Charger les données de test

```bash
php bin/console app:load-fixtures --reset
```

### 7. Lancer le serveur de développement

```bash
php -S localhost:8000 -t public/
# ou avec Symfony CLI :
symfony serve
```

### 8. Accéder à l'application

- Site : http://localhost:8000
- Admin : http://localhost:8000/admin
  - Email : `admin@pokeshop.fr` / Mot de passe : `Admin1234!`
- Client : `sacha@pokemon.fr` / `Sacha1234!`

---

## Fonctionnalités

### Côté client
- Catalogue de cartes avec filtres (type, rareté, série, prix, stock) et tri
- Recherche autocomplete en temps réel (AJAX)
- Fiche détaillée de chaque carte avec infos depuis PokeAPI
- Panier persistent (session) avec modification de quantité (AJAX)
- Inscription / connexion / profil
- Commande en ligne avec formulaire de livraison
- Avis et notes sur les cartes

### Côté admin (`/admin`)
- Dashboard avec statistiques et agrégations MongoDB
- CRUD complet : Cartes, Séries
- Gestion des commandes avec changement de statut (AJAX)
- Upload d'images pour les cartes

---

## Architecture MVC

```
src/
├── Controller/          # Couche Contrôleur
│   ├── HomeController.php
│   ├── CarteController.php
│   ├── PanierController.php
│   ├── CommandeController.php
│   ├── SecurityController.php
│   └── Admin/
│       ├── DashboardController.php
│       ├── CarteAdminController.php
│       ├── CommandeAdminController.php
│       └── SerieAdminController.php
├── Document/            # Couche Modèle (entités MongoDB ODM)
│   ├── Carte.php
│   ├── Serie.php
│   ├── Utilisateur.php
│   ├── Commande.php
│   ├── LigneCommande.php
│   └── Avis.php
├── Repository/          # Repository Pattern
├── Service/             # Services métier
│   ├── CartService.php       (Strategy Pattern)
│   ├── StockService.php      (Strategy Pattern)
│   ├── FileUploadService.php
│   └── PokemonApiService.php (CURL)
├── Factory/             # Factory Pattern
│   └── CommandeFactory.php
├── Form/                # Formulaires Symfony
├── Security/            # Provider d'authentification MongoDB
└── Command/             # Commandes Symfony (fixtures)
templates/               # Couche Vue (Twig)
```

---

## Design Patterns implémentés

1. **Repository Pattern** — `CarteRepository`, `CommandeRepository`, `UtilisateurRepository`, `SerieRepository`
   - Encapsule la logique d'accès aux données, découple les contrôleurs de MongoDB

2. **Factory Pattern** — `CommandeFactory`
   - Crée une `Commande` complète à partir du panier en session

3. **Strategy Pattern** — `CartService`, `StockService`
   - `CartService` : stratégie de panier en session (peut être remplacée par une stratégie BDD)
   - `StockService` : stratégie de réservation/libération de stock selon le statut

4. **Service Layer** — Tous les services dans `src/Service/`
   - Sépare la logique métier des contrôleurs

---

## MongoDB — Collections et relations

### Schéma des collections

```
series           cartes                utilisateurs       commandes
─────────        ─────────────────     ──────────────     ─────────────────────
_id              _id                   _id                _id
nom              nom                   email (unique)     reference (unique)
annee            serie → $ref series   password           utilisateur → $ref
description      typePokemon           prenom             clientNom
image            rarete                nom                clientEmail
createdAt        prix                  adresse            adresseLivraison
                 stock                 roles []            lignes [] (embedded)
                 description           actif                 ├ carte → $ref cartes
                 image                 createdAt             ├ carteNom (dénormalisé)
                 pv                                          ├ quantite
                 numeroPokedex                               └ prixUnitaire
                 avis [] (embedded)    total
                   ├ auteurNom        statut
                   ├ auteurEmail      createdAt
                   ├ note
                   └ commentaire
```

### Choix embedded vs referenced
- `avis` embedded dans `cartes` : accès fréquent et toujours avec la carte (dénormalisation justifiée)
- `lignes` embedded dans `commandes` : les lignes n'existent que dans le contexte d'une commande
- `serie` referenced dans `cartes` : une série peut avoir de nombreuses cartes, mise à jour centralisée
- `utilisateur` referenced dans `commandes` : permet la suppression d'un utilisateur sans perdre l'historique

---

## Tests

```bash
# Lancer tous les tests
php bin/phpunit

# Tests unitaires uniquement
php bin/phpunit tests/Unit/

# Tests fonctionnels
php bin/phpunit tests/Functional/

# Avec coverage
php bin/phpunit --coverage-html var/coverage
```

**30 tests** au total : 20 unitaires + 17 fonctionnels

---

## Scripts MongoDB

```bash
# Créer les index
mongosh pokemon_shop mongodb/01_indexes.js

# Exécuter les Map Reduce
mongosh pokemon_shop mongodb/02_map_reduce.js

# Configurer le sharding (cluster requis)
mongosh --port 27017 mongodb/03_sharding.js

# Configurer le Replica Set
mongosh --port 27017 mongodb/04_replica_set.js

# Requêtes complexes (démonstration)
mongosh pokemon_shop mongodb/05_queries_complexes.js
```

---

## Endpoints principaux

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/` | Page d'accueil |
| GET | `/cartes` | Catalogue avec filtres |
| GET | `/cartes/{id}` | Fiche carte |
| POST | `/panier/ajouter/{id}` | AJAX — Ajouter au panier |
| POST | `/panier/modifier/{id}` | AJAX — Modifier quantité |
| POST | `/panier/retirer/{id}` | AJAX — Retirer du panier |
| GET | `/recherche-ajax` | AJAX — Autocomplete |
| GET | `/api/pokemon-info/{nom}` | AJAX — Infos PokeAPI |
| GET | `/connexion` | Page de connexion |
| GET | `/inscription` | Page d'inscription |
| GET | `/admin` | Dashboard admin |
| GET | `/admin/cartes` | Liste cartes (admin) |
| POST | `/admin/commandes/{id}/statut` | AJAX — Changer statut commande |

---

## Troubleshooting

**Erreur `mongodb` extension non trouvée**
```bash
# Windows : télécharger depuis https://windows.php.net/downloads/pecl/releases/mongodb/
# Ajouter dans php.ini : extension=mongodb
```

**Erreur de connexion MongoDB**
```bash
# Vérifier que MongoDB est démarré
mongod --version
mongosh --eval "db.adminCommand('ping')"
```

**Cache Symfony à vider**
```bash
php bin/console cache:clear
```

---

## Structure Git recommandée

```
main          ← production stable
develop       ← branche de développement
feature/xxx   ← fonctionnalités
hotfix/xxx    ← correctifs urgents
```
