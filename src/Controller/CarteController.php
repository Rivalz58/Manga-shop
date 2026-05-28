<?php

namespace App\Controller;

use App\Document\Carte;
use App\Form\AvisType;
use App\Repository\CarteRepository;
use App\Repository\SerieRepository;
use App\Service\CartService;
use App\Service\PokemonApiService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cartes')]
class CarteController extends AbstractController
{
    private CarteRepository $carteRepo;
    private SerieRepository $serieRepo;

    public function __construct(private DocumentManager $dm)
    {
        $this->carteRepo = new CarteRepository($dm);
        $this->serieRepo = new SerieRepository($dm);
    }

    #[Route('', name: 'app_carte_index')]
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->query->get('search', ''),
            'type' => $request->query->get('type', ''),
            'rarete' => $request->query->get('rarete', ''),
            'serie' => $request->query->get('serie', ''),
            'prix_min' => $request->query->get('prix_min', ''),
            'prix_max' => $request->query->get('prix_max', ''),
            'en_stock' => $request->query->get('en_stock', ''),
        ];
        $sort = $request->query->get('sort', 'nom');
        $order = $request->query->get('order', 'asc');
        $page = max(1, (int) $request->query->get('page', 1));

        $result = $this->carteRepo->findWithFilters($filters, $sort, $order, $page, 12);
        $series = $this->serieRepo->findAllOrderedByAnnee();

        return $this->render('carte/index.html.twig', [
            'cartes' => $result['cartes'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $page,
            'filters' => $filters,
            'sort' => $sort,
            'order' => $order,
            'series' => $series,
            'types' => Carte::TYPES,
            'raretes' => Carte::RARETES,
        ]);
    }

    #[Route('/{id}', name: 'app_carte_show')]
    public function show(string $id, Request $request, PokemonApiService $pokemonApi): Response
    {
        $carte = $this->dm->find(Carte::class, $id);
        if (!$carte) {
            throw $this->createNotFoundException('Carte introuvable.');
        }

        $avisForm = $this->createForm(AvisType::class);
        $avisForm->handleRequest($request);

        if ($avisForm->isSubmitted() && $avisForm->isValid()) {
            $avis = $avisForm->getData();
            $carte->addAvis($avis);
            $this->dm->flush();

            $this->addFlash('success', 'Votre avis a été ajouté. Merci !');
            return $this->redirectToRoute('app_carte_show', ['id' => $id]);
        }

        // Récupération d'infos depuis PokeAPI (intégration CURL)
        $pokemonData = null;
        if ($carte->getNumeroPokedex() > 0) {
            $pokemonData = $pokemonApi->getPokemonInfo((string) $carte->getNumeroPokedex());
        }

        // Cartes similaires (même type)
        $cartesSimiliaires = array_filter(
            $this->carteRepo->findByRarete($carte->getRarete()),
            fn($c) => $c->getId() !== $carte->getId()
        );

        return $this->render('carte/show.html.twig', [
            'carte' => $carte,
            'avisForm' => $avisForm,
            'pokemonData' => $pokemonData,
            'cartesSimiliaires' => array_slice(array_values($cartesSimiliaires), 0, 4),
        ]);
    }

    #[Route('/ajax/panier-check/{id}', name: 'app_carte_stock_check', methods: ['GET'])]
    public function stockCheck(string $id): JsonResponse
    {
        $carte = $this->dm->find(Carte::class, $id);
        if (!$carte) {
            return $this->json(['error' => 'Carte introuvable'], 404);
        }

        return $this->json([
            'id' => $carte->getId(),
            'nom' => $carte->getNom(),
            'stock' => $carte->getStock(),
            'prix' => $carte->getPrix(),
            'enStock' => $carte->isEnStock(),
        ]);
    }
}
