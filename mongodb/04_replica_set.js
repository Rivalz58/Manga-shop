// ============================================================
// Script MongoDB : Configuration du Replica Set
// Démarrer les 3 membres AVANT d'exécuter ce script :
//
//   mongod --replSet rs0 --port 27017 --dbpath /data/rs0_0
//   mongod --replSet rs0 --port 27018 --dbpath /data/rs0_1
//   mongod --replSet rs0 --port 27019 --dbpath /data/rs0_2
//
// Usage: mongosh --port 27017 04_replica_set.js
// ============================================================

// Initialiser le replica set
rs.initiate({
    _id: "rs0",
    version: 1,
    members: [
        { _id: 0, host: "localhost:27017", priority: 2 },  // Primary préféré
        { _id: 1, host: "localhost:27018", priority: 1 },  // Secondary
        { _id: 2, host: "localhost:27019", priority: 0, votes: 1 }  // Arbiter ou Secondary
    ]
});

// Attendre l'élection du Primary
print("Attente de l'élection...");
let status;
let maxAttempts = 30;
for (let i = 0; i < maxAttempts; i++) {
    status = rs.status();
    const primary = status.members.find(m => m.stateStr === "PRIMARY");
    if (primary) {
        print(`✅ Primary élu : ${primary.name}`);
        break;
    }
    sleep(1000);
}

// Configurer la read preference pour les secondaries
// (lecture sur secondary autorisée pour les requêtes de reporting)
rs.config();

// Read Concern et Write Concern recommandés pour pokemon_shop
print("\n=== Configuration recommandée pour l'application ===");
print("Connection string: mongodb://localhost:27017,localhost:27018,localhost:27019/pokemon_shop?replicaSet=rs0");
print("Write concern: { w: 'majority', j: true }");
print("Read preference: { readPreference: 'secondaryPreferred' } pour les rapports");
print("Read preference: { readPreference: 'primary' } pour les commandes");

// Configurer les tags de replica set pour le routage des lectures
let cfg = rs.config();
cfg.members[0].tags = { dc: "paris", usage: "primary" };
cfg.members[1].tags = { dc: "lyon", usage: "reporting" };
cfg.members[2].tags = { dc: "marseille", usage: "backup" };
try { rs.reconfig(cfg); print("✅ Tags configurés."); } catch(e) { print("Note: reconfig nécessite d'être Primary."); }

print("\n✅ Replica Set configuré. Vérification :");
rs.status().members.forEach(m => print(`  ${m.name}: ${m.stateStr}`));
