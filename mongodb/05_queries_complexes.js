// ============================================================
// Script MongoDB : Requêtes complexes (agrégations, lookups, projections)
// Usage: mongosh pokemon_shop 05_queries_complexes.js
// ============================================================

use("pokemon_shop");

// ============================================================
// AGRÉGATION 1 : Top 5 cartes les plus populaires (avec avis)
// Pipeline : match → unwind → group → sort → limit
// ============================================================
print("=== Agrégation 1 : Top 5 cartes avec le plus d'avis ===");
db.cartes.aggregate([
    { $match: { "avis.0": { $exists: true } } },
    { $project: {
        nom: 1, typePokemon: 1, rarete: 1, prix: 1,
        nbAvis: { $size: "$avis" },
        noteMoyenne: { $avg: "$avis.note" }
    }},
    { $sort: { nbAvis: -1 } },
    { $limit: 5 },
    { $project: {
        nom: 1, typePokemon: 1, rarete: 1, prix: 1,
        nbAvis: 1,
        noteMoyenne: { $round: ["$noteMoyenne", 1] }
    }}
]).forEach(c => print(`  ${c.nom} (${c.rarete}): ${c.nbAvis} avis, note moy = ${c.noteMoyenne}`));

// ============================================================
// AGRÉGATION 2 : Lookup — Commandes avec détails utilisateur
// ============================================================
print("\n=== Agrégation 2 : Commandes avec infos utilisateur (lookup) ===");
db.commandes.aggregate([
    { $lookup: {
        from: "utilisateurs",
        localField: "utilisateur.$id",
        foreignField: "_id",
        as: "userDetails"
    }},
    { $unwind: { path: "$userDetails", preserveNullAndEmptyArrays: true } },
    { $project: {
        reference: 1, total: 1, statut: 1, createdAt: 1,
        clientNom: 1, clientEmail: 1,
        utilisateurNom: { $concat: ["$userDetails.prenom", " ", "$userDetails.nom"] },
        nbArticles: { $size: "$lignes" }
    }},
    { $sort: { createdAt: -1 } },
    { $limit: 5 }
]).forEach(c => print(`  ${c.reference} — ${c.utilisateurNom ?? c.clientNom} — ${c.total.toFixed(2)} € — ${c.statut}`));

// ============================================================
// AGRÉGATION 3 : Facet — Statistiques multi-dimensions en une seule passe
// ============================================================
print("\n=== Agrégation 3 : Facet — Stats multi-dimensions ===");
const facetResult = db.cartes.aggregate([
    { $facet: {
        parType: [
            { $group: { _id: "$typePokemon", count: { $sum: 1 }, prixMoyen: { $avg: "$prix" } } },
            { $sort: { count: -1 } }
        ],
        parRarete: [
            { $group: { _id: "$rarete", count: { $sum: 1 }, stockTotal: { $sum: "$stock" } } },
            { $sort: { count: -1 } }
        ],
        prixStats: [
            { $group: {
                _id: null,
                min: { $min: "$prix" }, max: { $max: "$prix" },
                moy: { $avg: "$prix" }, total: { $sum: 1 }
            }}
        ]
    }}
]).toArray()[0];

print("  Par type (top 3):");
facetResult.parType.slice(0, 3).forEach(t => print(`    ${t._id}: ${t.count} cartes, ${t.prixMoyen.toFixed(2)} € moy`));
print("  Par rareté:");
facetResult.parRarete.forEach(r => print(`    ${r._id}: ${r.count} cartes, stock = ${r.stockTotal}`));
print("  Prix globaux:");
const p = facetResult.prixStats[0];
print(`    Min: ${p.min} €, Max: ${p.max} €, Moy: ${p.moy.toFixed(2)} €, Total cartes: ${p.total}`);

// ============================================================
// AGRÉGATION 4 : Bucket — Répartition des cartes par tranche de prix
// ============================================================
print("\n=== Agrégation 4 : Bucket — Tranches de prix ===");
db.cartes.aggregate([
    { $bucket: {
        groupBy: "$prix",
        boundaries: [0, 1, 3, 5, 10, 20, 50, 200],
        default: "Autre",
        output: {
            count: { $sum: 1 },
            noms: { $push: "$nom" },
            stockTotal: { $sum: "$stock" }
        }
    }}
]).forEach(b => print(`  ${b._id} € — ${b.count} cartes, stock = ${b.stockTotal}`));

// ============================================================
// AGRÉGATION 5 : Graph Lookup simulation — Cartes par gamme de prix
// ============================================================
print("\n=== Agrégation 5 : Lookup croisé cartes × séries ===");
db.series.aggregate([
    { $lookup: {
        from: "cartes",
        let: { serieId: "$_id" },
        pipeline: [
            { $match: { $expr: { $eq: ["$serie.$id", "$$serieId"] } } },
            { $group: { _id: null, nbCartes: { $sum: 1 }, prixMoyen: { $avg: "$prix" }, stockTotal: { $sum: "$stock" } } }
        ],
        as: "stats"
    }},
    { $project: {
        nom: 1, annee: 1,
        nbCartes: { $ifNull: [{ $arrayElemAt: ["$stats.nbCartes", 0] }, 0] },
        prixMoyen: { $ifNull: [{ $arrayElemAt: ["$stats.prixMoyen", 0] }, 0] },
        stockTotal: { $ifNull: [{ $arrayElemAt: ["$stats.stockTotal", 0] }, 0] }
    }},
    { $sort: { annee: -1 } }
]).forEach(s => print(`  ${s.nom} (${s.annee}): ${s.nbCartes} cartes, prix moy = ${s.prixMoyen.toFixed(2)} €, stock = ${s.stockTotal}`));

print("\n✅ Toutes les requêtes complexes exécutées avec succès !");
