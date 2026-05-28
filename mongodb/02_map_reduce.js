// ============================================================
// Script MongoDB : Opérations Map Reduce
// Usage: mongosh pokemon_shop 02_map_reduce.js
// ============================================================

use("pokemon_shop");

// ============================================================
// MAP REDUCE 1 : Chiffre d'affaires potentiel par type de Pokémon
// Calcule pour chaque type : stock total, valeur totale, prix moyen
// ============================================================

print("=== Map Reduce 1 : CA potentiel par type ===");

const mrCAParType = db.cartes.mapReduce(
    // Map : émet (type_pokemon -> {caPotentiel, stock, count})
    function() {
        emit(this.typePokemon, {
            caPotentiel: this.prix * this.stock,
            stock: this.stock,
            count: 1,
            prixTotal: this.prix
        });
    },
    // Reduce : somme les valeurs par type
    function(key, values) {
        let result = { caPotentiel: 0, stock: 0, count: 0, prixTotal: 0 };
        values.forEach(v => {
            result.caPotentiel += v.caPotentiel;
            result.stock += v.stock;
            result.count += v.count;
            result.prixTotal += v.prixTotal;
        });
        return result;
    },
    {
        out: { replace: "mr_ca_par_type" },
        // Finalize : calcule le prix moyen
        finalize: function(key, reducedVal) {
            reducedVal.prixMoyen = reducedVal.count > 0
                ? Math.round((reducedVal.prixTotal / reducedVal.count) * 100) / 100
                : 0;
            return reducedVal;
        }
    }
);

print("Résultats CA par type:");
db.mr_ca_par_type.find().sort({ "value.caPotentiel": -1 }).forEach(doc => {
    print(`  ${doc._id}: CA potentiel = ${doc.value.caPotentiel.toFixed(2)} €, Stock = ${doc.value.stock}, Prix moy = ${doc.value.prixMoyen} €`);
});

// ============================================================
// MAP REDUCE 2 : Distribution des raretés dans le catalogue
// Calcule pour chaque rareté : nombre de cartes, prix min/max/moyen, stock total
// ============================================================

print("\n=== Map Reduce 2 : Distribution des raretés ===");

const mrDistribRarete = db.cartes.mapReduce(
    // Map : émet (rareté -> statistiques de la carte)
    function() {
        emit(this.rarete, {
            count: 1,
            prixMin: this.prix,
            prixMax: this.prix,
            prixSum: this.prix,
            stockTotal: this.stock,
            nbEnStock: this.stock > 0 ? 1 : 0
        });
    },
    // Reduce : agrège les statistiques par rareté
    function(key, values) {
        let result = {
            count: 0,
            prixMin: Infinity,
            prixMax: -Infinity,
            prixSum: 0,
            stockTotal: 0,
            nbEnStock: 0
        };
        values.forEach(v => {
            result.count += v.count;
            result.prixMin = Math.min(result.prixMin, v.prixMin);
            result.prixMax = Math.max(result.prixMax, v.prixMax);
            result.prixSum += v.prixSum;
            result.stockTotal += v.stockTotal;
            result.nbEnStock += v.nbEnStock;
        });
        return result;
    },
    {
        out: { replace: "mr_distribution_rarete" },
        finalize: function(key, reducedVal) {
            reducedVal.prixMoyen = reducedVal.count > 0
                ? Math.round((reducedVal.prixSum / reducedVal.count) * 100) / 100
                : 0;
            reducedVal.tauxDisponibilite = reducedVal.count > 0
                ? Math.round((reducedVal.nbEnStock / reducedVal.count) * 100)
                : 0;
            delete reducedVal.prixSum;
            return reducedVal;
        }
    }
);

print("Résultats distribution des raretés:");
db.mr_distribution_rarete.find().forEach(doc => {
    print(`  ${doc._id}: ${doc.value.count} cartes, Prix: ${doc.value.prixMin}€ - ${doc.value.prixMax}€ (moy: ${doc.value.prixMoyen}€), Dispo: ${doc.value.tauxDisponibilite}%`);
});

// ============================================================
// MAP REDUCE 3 : Analyse des commandes par mois
// ============================================================

print("\n=== Map Reduce 3 : CA commandes par mois ===");

db.commandes.mapReduce(
    function() {
        if (this.statut !== 'Annulée') {
            const mois = this.createdAt.getMonth() + 1;
            const annee = this.createdAt.getFullYear();
            emit(`${annee}-${String(mois).padStart(2, '0')}`, {
                ca: this.total,
                nbCommandes: 1,
                panierMoyen: this.total
            });
        }
    },
    function(key, values) {
        let result = { ca: 0, nbCommandes: 0, panierMoyen: 0 };
        values.forEach(v => { result.ca += v.ca; result.nbCommandes += v.nbCommandes; result.panierMoyen += v.panierMoyen; });
        return result;
    },
    {
        out: { replace: "mr_ca_mensuel" },
        finalize: function(key, r) {
            r.panierMoyen = r.nbCommandes > 0 ? Math.round((r.panierMoyen / r.nbCommandes) * 100) / 100 : 0;
            return r;
        }
    }
);

print("CA mensuel:");
db.mr_ca_mensuel.find().sort({ _id: -1 }).limit(6).forEach(doc => {
    print(`  ${doc._id}: CA = ${doc.value.ca.toFixed(2)} €, ${doc.value.nbCommandes} commandes, Panier moy = ${doc.value.panierMoyen} €`);
});

print("\n✅ Map Reduce terminés. Collections créées: mr_ca_par_type, mr_distribution_rarete, mr_ca_mensuel");
