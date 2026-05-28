<?php

namespace App\Tests\Unit;

use App\Document\Carte;
use App\Document\LigneCommande;
use PHPUnit\Framework\TestCase;

class LigneCommandeTest extends TestCase
{
    // TEST 28 : getSousTotal() calcule correctement
    public function testGetSousTotal(): void
    {
        $carte = new Carte();
        $carte->setPrix(9.99)->setNom('Dracaufeu');

        $ligne = new LigneCommande();
        $ligne->setCarte($carte)->setQuantite(3)->setPrixUnitaire(9.99);

        $this->assertEqualsWithDelta(29.97, $ligne->getSousTotal(), 0.001);
    }

    // TEST 29 : setCarte() copie nom et image
    public function testSetCarteCopieDonnees(): void
    {
        $carte = new Carte();
        $carte->setNom('Pikachu')->setImage('pikachu.png');

        $ligne = new LigneCommande();
        $ligne->setCarte($carte);

        $this->assertSame('Pikachu', $ligne->getCarteNom());
        $this->assertSame('pikachu.png', $ligne->getCarteImage());
    }

    // TEST 30 : Sous-total nul si quantité 0
    public function testSousTotalNulAvecQuantiteZero(): void
    {
        $ligne = new LigneCommande();
        $ligne->setQuantite(0)->setPrixUnitaire(5.00);
        $this->assertSame(0.0, $ligne->getSousTotal());
    }
}
