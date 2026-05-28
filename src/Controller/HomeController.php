<?php

namespace App\Controller;

use App\Repository\CarteRepository;
use App\Repository\SerieRepository;
use App\Service\PokemonApiService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private DocumentManager $dm,
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $carteRepo = new CarteRepository($this->dm);
        $serieRepo = new SerieRepository($this->dm);

        $cartesRecentes = $carteRepo->findCartesEnStock(8);
        $cartesUltraRares = $carteRepo->findByRarete('Ultra Rare');
        $series = $serieRepo->findAllOrderedByAnnee();
        $statsParType = $carteRepo->getStatistiquesParType();

        return $this->render('home/index.html.twig', [
            'cartesRecentes' => $cartesRecentes,
            'cartesUltraRares' => array_slice($cartesUltraRares, 0, 4),
            'series' => array_slice($series, 0, 6),
            'statsParType' => $statsParType,
        ]);
    }

    #[Route('/recherche-ajax', name: 'app_recherche_ajax', methods: ['GET'])]
    public function rechercheAjax(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $carteRepo = new CarteRepository($this->dm);
        $cartes = $carteRepo->searchByNom($query);

        $results = array_map(fn($c) => [
            'id' => $c->getId(),
            'nom' => $c->getNom(),
            'prix' => $c->getPrix(),
            'image' => $c->getImage(),
            'rarete' => $c->getRarete(),
            'url' => $this->generateUrl('app_carte_show', ['id' => $c->getId()]),
        ], $cartes);

        return $this->json($results);
    }

    #[Route('/api/pokemon-info/{nom}', name: 'app_api_pokemon_info', methods: ['GET'])]
    public function pokemonInfo(string $nom, PokemonApiService $pokemonApi): JsonResponse
    {
        $info = $pokemonApi->getPokemonInfo($nom);

        if (!$info) {
            return $this->json(['error' => 'Pokémon non trouvé'], 404);
        }

        return $this->json([
            'nom' => $info['name'] ?? $nom,
            'sprite' => $info['sprites']['front_default'] ?? null,
            'types' => array_map(fn($t) => $t['type']['name'], $info['types'] ?? []),
            'stats' => array_map(fn($s) => [
                'nom' => $s['stat']['name'],
                'valeur' => $s['base_stat'],
            ], $info['stats'] ?? []),
        ]);
    }
}
