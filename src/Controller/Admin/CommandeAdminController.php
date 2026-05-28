<?php

namespace App\Controller\Admin;

use App\Document\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commandes')]
#[IsGranted('ROLE_ADMIN')]
class CommandeAdminController extends AbstractController
{
    private CommandeRepository $repo;

    public function __construct(private DocumentManager $dm)
    {
        $this->repo = new CommandeRepository($dm);
    }

    #[Route('', name: 'app_admin_commande_index')]
    public function index(Request $request): Response
    {
        $filters = [
            'statut' => $request->query->get('statut', ''),
            'reference' => $request->query->get('reference', ''),
            'email' => $request->query->get('email', ''),
        ];
        $page = max(1, (int) $request->query->get('page', 1));
        $result = $this->repo->findWithFilters($filters, $page);

        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $result['commandes'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $page,
            'filters' => $filters,
            'statuts' => Commande::STATUTS,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commande_show')]
    public function show(string $id): Response
    {
        $commande = $this->dm->find(Commande::class, $id);
        if (!$commande) throw $this->createNotFoundException('Commande introuvable.');

        return $this->render('admin/commande/show.html.twig', [
            'commande' => $commande,
            'statuts' => Commande::STATUTS,
        ]);
    }

    #[Route('/{id}/statut', name: 'app_admin_commande_statut', methods: ['POST'])]
    public function updateStatut(string $id, Request $request): JsonResponse
    {
        $commande = $this->dm->find(Commande::class, $id);
        if (!$commande) return $this->json(['error' => 'Non trouvé'], 404);

        $statut = $request->request->get('statut');
        if (!in_array($statut, Commande::STATUTS)) {
            return $this->json(['error' => 'Statut invalide'], 400);
        }

        $commande->setStatut($statut);
        $this->dm->flush();

        return $this->json([
            'success' => true,
            'statut' => $statut,
            'message' => 'Statut mis à jour.',
        ]);
    }
}
