<?php

namespace App\Controller\Admin;

use App\Repository\UtilisateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_ADMIN')]
class UtilisateurAdminController extends AbstractController
{
    private UtilisateurRepository $repo;

    public function __construct(private DocumentManager $dm)
    {
        $this->repo = new UtilisateurRepository($dm);
    }

    #[Route('', name: 'app_admin_utilisateur_index')]
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->query->get('search', ''),
            'role'   => $request->query->get('role', ''),
        ];
        $page = max(1, (int) $request->query->get('page', 1));

        $result = $this->repo->findWithFilters($filters, $page, 20);

        return $this->render('admin/utilisateur/index.html.twig', [
            'utilisateurs' => $result['utilisateurs'],
            'total'        => $result['total'],
            'pages'        => $result['pages'],
            'page'         => $result['page'],
            'filters'      => $filters,
        ]);
    }
}
