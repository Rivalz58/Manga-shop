// ============================================================
// Script MongoDB : Création des index pour pokemon_shop
// Usage: mongosh pokemon_shop 01_indexes.js
// ============================================================

use("pokemon_shop");

// === Collection cartes ===
// Index texte pour la recherche full-text
db.cartes.createIndex(
    { nom: "text", description: "text", typePokemon: "text" },
    { name: "idx_search_text", weights: { nom: 10, typePokemon: 3, description: 1 } }
);

// Index composite rareté + prix (pour les filtres combinés)
db.cartes.createIndex(
    { rarete: 1, prix: 1 },
    { name: "idx_rarete_prix" }
);

// Index sur le type (filtre fréquent)
db.cartes.createIndex(
    { typePokemon: 1 },
    { name: "idx_type" }
);

// Index sur le stock (pour les filtres "en stock")
db.cartes.createIndex(
    { stock: 1 },
    { name: "idx_stock" }
);

// Index sur la date de création (tri "nouveautés")
db.cartes.createIndex(
    { createdAt: -1 },
    { name: "idx_created_at_desc" }
);

// Index pour les requêtes par série
db.cartes.createIndex(
    { "serie.$id": 1 },
    { name: "idx_serie_ref" }
);

// === Collection utilisateurs ===
// Index unique sur l'email (authentification)
db.utilisateurs.createIndex(
    { email: 1 },
    { unique: true, name: "idx_email_unique" }
);

// Index sur les rôles
db.utilisateurs.createIndex(
    { roles: 1 },
    { name: "idx_roles" }
);

// === Collection commandes ===
// Index sur la date (tri chronologique)
db.commandes.createIndex(
    { createdAt: -1 },
    { name: "idx_commandes_date" }
);

// Index sur le statut (filtres admin)
db.commandes.createIndex(
    { statut: 1 },
    { name: "idx_statut" }
);

// Index sur la référence (recherche par référence)
db.commandes.createIndex(
    { reference: 1 },
    { unique: true, name: "idx_reference_unique" }
);

// Index sur l'email client
db.commandes.createIndex(
    { clientEmail: 1 },
    { name: "idx_client_email" }
);

// Index sur l'utilisateur (pour "mes commandes")
db.commandes.createIndex(
    { "utilisateur.$id": 1, createdAt: -1 },
    { name: "idx_user_commandes" }
);

// === Collection series ===
db.series.createIndex(
    { nom: 1 },
    { unique: true, name: "idx_serie_nom_unique" }
);
db.series.createIndex(
    { annee: -1 },
    { name: "idx_serie_annee" }
);

print("✅ Tous les index créés avec succès !");
db.cartes.getIndexes().forEach(i => print("  Carte index:", i.name));
db.commandes.getIndexes().forEach(i => print("  Commande index:", i.name));
