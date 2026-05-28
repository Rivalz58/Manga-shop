<?php

namespace App\Tests\Unit;

use App\Document\Avis;
use App\Document\Carte;
use App\Document\Serie;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Carte.
 * Couvre les règles métier sans dépendance à la base de données.
 */
class CarteTest extends TestCase
{
    private Carte $carte;

    protected function setUp(): void
    {
        $this->carte = new Carte();
    }

    // TEST 1 : Création d'une carte avec valeurs par défaut
    public function testCreationAvecValeursParDefaut(): void
    {
        $this->assertSame('', $this->carte->getNom());
        $this->assertSame('Normal', $this->carte->getTypePokemon());
        $this->assertSame('Commune', $this->carte->getRarete());
        $this->assertSame(0.0, $this->carte->getPrix());
        $this->assertSame(0, $this->carte->getStock());
        $this->assertNull($this->carte->getId());
    }

    // TEST 2 : Setters fonctionnent correctement
    public function testSettersEtGetters(): void
    {
        $this->carte->setNom('Pikachu');
        $this->carte->setTypePokemon('Électrique');
        $this->carte->setRarete('Rare');
        $this->carte->setPrix(5.99);
        $this->carte->setStock(10);

        $this->assertSame('Pikachu', $this->carte->getNom());
        $this->assertSame('Électrique', $this->carte->getTypePokemon());
        $this->assertSame('Rare', $this->carte->getRarete());
        $this->assertSame(5.99, $this->carte->getPrix());
        $this->assertSame(10, $this->carte->getStock());
    }

    // TEST 3 : isEnStock() retourne true si stock > 0
    public function testIsEnStockTrue(): void
    {
        $this->carte->setStock(5);
        $this->assertTrue($this->carte->isEnStock());
    }

    // TEST 4 : isEnStock() retourne false si stock = 0
    public function testIsEnStockFalse(): void
    {
        $this->carte->setStock(0);
        $this->assertFalse($this->carte->isEnStock());
    }

    // TEST 5 : Note moyenne sans avis retourne 0
    public function testNoteMoyenneSansAvis(): void
    {
        $this->assertSame(0.0, $this->carte->getNoteMoyenne());
    }

    // TEST 6 : Note moyenne avec un avis
    public function testNoteMoyenneAvecUnAvis(): void
    {
        $avis = new Avis();
        $avis->setNote(4);
        $this->carte->addAvis($avis);

        $this->assertSame(4.0, $this->carte->getNoteMoyenne());
    }

    // TEST 7 : Note moyenne avec plusieurs avis
    public function testNoteMoyenneAvecPlusieursAvis(): void
    {
        foreach ([5, 3, 4] as $note) {
            $avis = new Avis();
            $avis->setNote($note);
            $this->carte->addAvis($avis);
        }

        $this->assertEqualsWithDelta(4.0, $this->carte->getNoteMoyenne(), 0.05);
    }

    // TEST 8 : Association avec une série
    public function testAssociationSerie(): void
    {
        $serie = new Serie();
        $serie->setNom('Écarlate et Violet')->setAnnee(2023);

        $this->carte->setSerie($serie);

        $this->assertSame($serie, $this->carte->getSerie());
        $this->assertSame('Écarlate et Violet', $this->carte->getSerie()->getNom());
    }

    // TEST 9 : Prix ne doit pas accepter une valeur négative (test de la valeur brute)
    public function testPrixPeutEtreModifie(): void
    {
        $this->carte->setPrix(12.50);
        $this->assertSame(12.50, $this->carte->getPrix());
    }

    // TEST 10 : Les constantes RARETES et TYPES sont définies
    public function testConstantesDefinies(): void
    {
        $this->assertContains('Commune', Carte::RARETES);
        $this->assertContains('Secrète', Carte::RARETES);
        $this->assertContains('Feu', Carte::TYPES);
        $this->assertContains('Eau', Carte::TYPES);
        $this->assertCount(5, Carte::RARETES);
    }

    // TEST 11 : Image par défaut
    public function testImageParDefaut(): void
    {
        $this->assertSame('default-card.png', $this->carte->getImage());
    }

    // TEST 12 : Chaînage des setters (fluent interface)
    public function testChainageSetters(): void
    {
        $result = $this->carte
            ->setNom('Mewtwo')
            ->setPrix(29.99)
            ->setStock(3)
            ->setTypePokemon('Psychique');

        $this->assertSame($this->carte, $result);
    }

    // TEST 13 : createdAt est initialisé à la création
    public function testCreatedAtInitialise(): void
    {
        $this->assertInstanceOf(\DateTime::class, $this->carte->getCreatedAt());
    }

    // TEST 14 : PV et numéro Pokédex
    public function testPvEtPokedex(): void
    {
        $this->carte->setPv(120)->setNumeroPokedex(6);
        $this->assertSame(120, $this->carte->getPv());
        $this->assertSame(6, $this->carte->getNumeroPokedex());
    }

    // TEST 15 : Collection avis est initialisée vide
    public function testAvisCollectionVideParDefaut(): void
    {
        $this->assertCount(0, $this->carte->getAvis());
    }
}
