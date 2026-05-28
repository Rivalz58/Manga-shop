<?php

namespace App\Controller\Admin;

use App\Document\Carte;
use App\Document\Serie;
use App\Form\CarteType;
use App\Repository\CarteRepository;
use App\Service\FileUploadService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/cartes')]
#[IsGranted('ROLE_ADMIN')]
class CarteAdminController extends AbstractController
{
    private CarteRepository $repo;

    public function __construct(
        private DocumentManager $dm,
        private FileUploadService $fileUploadService,
    ) {
        $this->repo = new CarteRepository($dm);
    }

    #[Route('', name: 'app_admin_carte_index')]
    public function index(Request $request): Response
    {
        $filters = ['search' => $request->query->get('search', '')];
        $page = max(1, (int) $request->query->get('page', 1));
        $result = $this->repo->findWithFilters($filters, 'nom', 'asc', $page, 20);

        return $this->render('admin/carte/index.html.twig', [
            'cartes' => $result['cartes'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $page,
            'filters' => $filters,
        ]);
    }

    #[Route('/nouvelle', name: 'app_admin_carte_new')]
    public function new(Request $request): Response
    {
        $carte = new Carte();
        $form = $this->createForm(CarteType::class, $carte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serieId = $form->get('serieId')->getData();
            $serie = $this->dm->find(Serie::class, $serieId);
            $carte->setSerie($serie);

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $carte->setImage($this->fileUploadService->upload($imageFile));
            }

            $this->dm->persist($carte);
            $this->dm->flush();

            $this->addFlash('success', 'Carte "' . $carte->getNom() . '" créée avec succès.');
            return $this->redirectToRoute('app_admin_carte_index');
        }

        return $this->render('admin/carte/form.html.twig', [
            'form' => $form,
            'carte' => $carte,
            'titre' => 'Nouvelle carte',
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_carte_edit')]
    public function edit(string $id, Request $request): Response
    {
        $carte = $this->dm->find(Carte::class, $id);
        if (!$carte) throw $this->createNotFoundException('Carte introuvable.');

        $form = $this->createForm(CarteType::class, $carte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serieId = $form->get('serieId')->getData();
            $serie = $this->dm->find(Serie::class, $serieId);
            $carte->setSerie($serie);

            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $this->fileUploadService->delete($carte->getImage());
                $carte->setImage($this->fileUploadService->upload($imageFile));
            }

            $this->dm->flush();

            $this->addFlash('success', 'Carte modifiée.');
            return $this->redirectToRoute('app_admin_carte_index');
        }

        return $this->render('admin/carte/form.html.twig', [
            'form' => $form,
            'carte' => $carte,
            'titre' => 'Modifier ' . $carte->getNom(),
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_carte_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_carte_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_carte_index');
        }

        $carte = $this->dm->find(Carte::class, $id);
        if ($carte) {
            $this->fileUploadService->delete($carte->getImage());
            $this->dm->remove($carte);
            $this->dm->flush();
            $this->addFlash('success', 'Carte supprimée.');
        }

        return $this->redirectToRoute('app_admin_carte_index');
    }
}
