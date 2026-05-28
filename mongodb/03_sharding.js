// ============================================================
// Script MongoDB : Configuration du Sharding
// Pour un cluster MongoDB avec mongos + config servers + shards
// Usage: mongosh --port 27017 03_sharding.js (sur le mongos)
// ============================================================

// PRÉREQUIS : Démarrer un cluster avec :
// - 1 mongos (port 27017)
// - 1 config server replica set (port 27019)
// - 2+ shards (ports 27020, 27021)

// Activer le sharding sur la base de données
sh.enableSharding("pokemon_shop");

// ============================================================
// SHARDING SUR LA COLLECTION cartes
// Shard key : { rarete: 1, _id: 1 }
// Justification : les requêtes filtrent souvent par rareté,
// distribuer par rareté répartit la charge naturellement.
// ============================================================

sh.shardCollection(
    "pokemon_shop.cartes",
    { rarete: 1, _id: 1 }
);

// Zones de sharding pour répartir par popularité :
// Shard 0 : Communes et Peu communes (volume élevé, faible prix)
// Shard 1 : Rares, Ultra Rares et Secrètes (volume faible, prix élevé)
sh.addShardTag("shard0", "FREQUENT");
sh.addShardTag("shard1", "RARE");

sh.addTagRange(
    "pokemon_shop.cartes",
    { rarete: "Commune", _id: MinKey },
    { rarete: "Peu commune", _id: MaxKey },
    "FREQUENT"
);
sh.addTagRange(
    "pokemon_shop.cartes",
    { rarete: "Rare", _id: MinKey },
    { rarete: "Secrète", _id: MaxKey },
    "RARE"
);

// ============================================================
// SHARDING SUR LA COLLECTION commandes
// Shard key : { createdAt: 1 }
// Justification : les commandes sont principalement requêtées
// par plage de dates (reporting mensuel, historique client).
// ============================================================

sh.shardCollection(
    "pokemon_shop.commandes",
    { createdAt: 1, _id: 1 }
);

// ============================================================
// SHARDING SUR LA COLLECTION utilisateurs
// Shard key hasché sur _id pour distribution uniforme
// ============================================================

sh.shardCollection(
    "pokemon_shop.utilisateurs",
    { _id: "hashed" }
);

// Vérification
print("\n=== État du sharding ===");
sh.status();

print("\n=== Chunks par collection ===");
db.getSiblingDB("config").chunks.aggregate([
    { $group: { _id: "$ns", count: { $sum: 1 } } }
]).forEach(r => print(`  ${r._id}: ${r.count} chunks`));

print("\n✅ Configuration du sharding terminée !");
