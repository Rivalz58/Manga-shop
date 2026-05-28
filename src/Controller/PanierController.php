<?php

namespace App\Controller;

use App\Document\Carte;
use App\Service\CartService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private DocumentManager $dm,
    ) {}

    #[Route('', name: 'app_panier')]
    public function index(): Response
    {
        $panierDetails = $this->cartService->getPanierAvecDetails();

        return $this->render('panier/index.html.twig', [
            'items' => $panierDetails['items'],
            'total' => $panierDetails['total'],
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_panier_ajouter', methods: ['POST'])]
    public function ajouter(string $id, Request $request): JsonResponse
    {
        $carte = $this->dm->find(Carte::class, $id);
        if (!$carte || !$carte->isEnStock()) {
            return $this->json(['success' => false, 'message' => 'Carte indisponible'], 400);
        }

        $quantite = max(1, (int) $request->request->get('quantite', 1));
        $this->cartService->ajouterCarte($id, $quantite);

        return $this->json([
            'success' => true,
            'message' => sprintf('%s ajouté(e) au panier !', $carte->getNom()),
            'nombreArticles' => $this->cartService->getNombreArticles(),
        ]);
    }

    #[Route('/retirer/{id}', name: 'app_panier_retirer', methods: ['POST'])]
    public function retirer(string $id): JsonResponse
    {
        $this->cartService->retirerCarte($id);

        $panierDetails = $this->cartService->getPanierAvecDetails();

        return $this->json([
            'success' => true,
            'nombreArticles' => $this->cartService->getNombreArticles(),
            'total' => $panierDetails['total'],
        ]);
    }

    #[Route('/modifier/{id}', name: 'app_panier_modifier', methods: ['POST'])]
    public function modifier(string $id, Request $request): JsonResponse
    {
        $quantite = max(0, (int) $request->request->get('quantite', 1));

        $carte = $this->dm->find(Carte::class, $id);
        if ($carte && $quantite > $carte->getStock()) {
            return $this->json([
                'success' => false,
                'message' => 'Stock insuffisant (disponible: ' . $carte->getStock() . ')',
            ], 400);
        }

        $this->cartService->modifierQuantite($id, $quantite);
        $panierDetails = $this->cartService->getPanierAvecDetails();

        return $this->json([
            'success' => true,
            'nombreArticles' => $this->cartService->getNombreArticles(),
            'total' => $panierDetails['total'],
        ]);
    }

    #[Route('/vider', name: 'app_panier_vider', methods: ['POST'])]
    public function vider(): JsonResponse
    {
        $this->cartService->vider();
        return $this->json(['success' => true, 'nombreArticles' => 0]);
    }

    #[Route('/count', name: 'app_panier_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->cartService->getNombreArticles()]);
    }
}
