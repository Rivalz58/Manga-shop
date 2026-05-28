<?php

namespace App\Tests\Unit;

use App\Document\Carte;
use App\Document\Commande;
use App\Document\LigneCommande;
use PHPUnit\Framework\TestCase;

class CommandeTest extends TestCase
{
    // TEST 16 : Création avec référence auto-générée
    public function testReferenceAutoGeneree(): void
    {
        $commande = new Commande();
        $this->assertStringStartsWith('CMD-', $commande->getReference());
        $this->assertSame(12, strlen($commande->getReference()));
    }

    // TEST 17 : Statut par défaut est "En attente"
    public function testStatutDefaut(): void
    {
        $commande = new Commande();
        $this->assertSame('En attente', $commande->getStatut());
    }

    // TEST 18 : Calcul du total avec plusieurs lignes
    public function testCalculerTotal(): void
    {
        $commande = new Commande();

        $carte1 = new Carte(); $carte1->setPrix(5.00);
        $ligne1 = new LigneCommande();
        $ligne1->setCarte($carte1)->setQuantite(2)->setPrixUnitaire(5.00);

        $carte2 = new Carte(); $carte2->setPrix(12.99);
        $ligne2 = new LigneCommande();
        $ligne2->setCarte($carte2)->setQuantite(1)->setPrixUnitaire(12.99);

        $commande->addLigne($ligne1)->addLigne($ligne2);
        $commande->calculerTotal();

        $this->assertEqualsWithDelta(22.99, $commande->getTotal(), 0.001);
    }

    // TEST 19 : getNombreLignes() correct
    public function testNombreLignes(): void
    {
        $commande = new Commande();
        $this->assertSame(0, $commande->getNombreLignes());

        $ligne = new LigneCommande();
        $commande->addLigne($ligne);
        $this->assertSame(1, $commande->getNombreLignes());
    }

    // TEST 20 : Références uniques entre deux commandes
    public function testReferencesUniques(): void
    {
        $cmd1 = new Commande();
        $cmd2 = new Commande();
        $this->assertNotEquals($cmd1->getReference(), $cmd2->getReference());
    }
}
