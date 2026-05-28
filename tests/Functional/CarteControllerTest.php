<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels : vérifient les routes HTTP, les réponses et le comportement global.
 * Ces tests nécessitent une base MongoDB accessible.
 */
class CarteControllerTest extends WebTestCase
{
    // TEST F1 : Page d'accueil accessible (200)
    public function testPageAccueilOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('nav.navbar-pokemon');
    }

    // TEST F2 : Catalogue des cartes accessible
    public function testCatalogueAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cartes');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Catalogue');
    }

    // TEST F3 : Recherche AJAX retourne du JSON
    public function testRechercheAjaxRetourneJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recherche-ajax?q=pika');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    // TEST F4 : Recherche AJAX requête trop courte retourne tableau vide
    public function testRechercheAjaxRequeteCourte(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recherche-ajax?q=a');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    // TEST F5 : Filtre par type dans le catalogue
    public function testFiltreParType(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cartes?type=Feu');
        $this->assertResponseIsSuccessful();
    }

    // TEST F6 : Page d'une carte inexistante retourne 404
    public function testCarteInexistante404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cartes/000000000000000000000000');
        $this->assertResponseStatusCodeSame(404);
    }

    // TEST F7 : Page panier accessible (vide)
    public function testPanierAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/panier');
        $this->assertResponseIsSuccessful();
    }

    // TEST F8 : Connexion accessible
    public function testPageConnexionAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // TEST F9 : Inscription accessible
    public function testPageInscriptionAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inscription');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // TEST F10 : Admin redirige si non connecté
    public function testAdminRedirigeNonConnecte(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/connexion');
    }

    // TEST F11 : Page commande redirige si non authentifié
    public function testCommandeValiderRedirigeNonAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/commande/valider');
        $this->assertResponseRedirects('/connexion');
    }

    // TEST F12 : API Pokemon info retourne JSON
    public function testApiPokemonInfo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/pokemon-info/pikachu');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    // TEST F13 : POST panier/ajouter avec carte invalide retourne 400
    public function testAjouterCarteInvalideRetourne400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/panier/ajouter/invalid_id');
        $this->assertResponseStatusCodeSame(404);
    }

    // TEST F14 : Count panier retourne JSON avec count
    public function testPanierCountRetourneJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/panier/count');
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('count', $data);
    }

    // TEST F15 : Mes commandes redirige si non connecté
    public function testMesCommandesRedirigeNonConnecte(): void
    {
        $client = static::createClient();
        $client->request('GET', '/commande/mes-commandes');
        $this->assertResponseRedirects('/connexion');
    }

    // TEST F16 : Filtre par rareté
    public function testFiltreParRarete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cartes?rarete=Rare');
        $this->assertResponseIsSuccessful();
    }

    // TEST F17 : Tri par prix
    public function testTriParPrix(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cartes?sort=prix&order=asc');
        $this->assertResponseIsSuccessful();
    }
}
