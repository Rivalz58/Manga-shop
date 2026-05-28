<?php

namespace App\Controller\Admin;

use App\Document\Serie;
use App\Form\SerieType;
use App\Repository\SerieRepository;
use App\Service\FileUploadService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/series')]
#[IsGranted('ROLE_ADMIN')]
class SerieAdminController extends AbstractController
{
    public function __construct(
        private DocumentManager $dm,
        private FileUploadService $fileUploadService,
    ) {}

    #[Route('', name: 'app_admin_serie_index')]
    public function index(): Response
    {
        $repo = new SerieRepository($this->dm);
        return $this->render('admin/serie/index.html.twig', [
            'series' => $repo->findAllOrderedByAnnee(),
        ]);
    }

    #[Route('/nouvelle', name: 'app_admin_serie_new')]
    public function new(Request $request): Response
    {
        $serie = new Serie();
        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $serie->setImage($this->fileUploadService->upload($imageFile, 'serie'));
            }
            $this->dm->persist($serie);
            $this->dm->flush();
            $this->addFlash('success', 'Série créée.');
            return $this->redirectToRoute('app_admin_serie_index');
        }

        return $this->render('admin/serie/form.html.twig', ['form' => $form, 'titre' => 'Nouvelle série']);
    }

    #[Route('/{id}/modifier', name: 'app_admin_serie_edit')]
    public function edit(string $id, Request $request): Response
    {
        $serie = $this->dm->find(Serie::class, $id);
        if (!$serie) throw $this->createNotFoundException();

        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $this->fileUploadService->delete($serie->getImage());
                $serie->setImage($this->fileUploadService->upload($imageFile, 'serie'));
            }
            $this->dm->flush();
            $this->addFlash('success', 'Série modifiée.');
            return $this->redirectToRoute('app_admin_serie_index');
        }

        return $this->render('admin/serie/form.html.twig', ['form' => $form, 'titre' => 'Modifier ' . $serie->getNom()]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_serie_delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_serie_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_admin_serie_index');
        }
        $serie = $this->dm->find(Serie::class, $id);
        if ($serie) {
            $this->dm->remove($serie);
            $this->dm->flush();
            $this->addFlash('success', 'Série supprimée.');
        }
        return $this->redirectToRoute('app_admin_serie_index');
    }
}
