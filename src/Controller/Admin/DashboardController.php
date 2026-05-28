<?php

namespace App\Controller\Admin;

use App\Document\Carte;
use App\Repository\CarteRepository;
use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    public function __construct(private DocumentManager $dm) {}

    #[Route('', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        $carteRepo = new CarteRepository($this->dm);
        $commandeRepo = new CommandeRepository($this->dm);
        $userRepo = new UtilisateurRepository($this->dm);

        $statsCommandes = $commandeRepo->getStatistiquesGlobales();
        $statsTypes = $carteRepo->getStatistiquesParType();
        $caParMois = $commandeRepo->getChiffreAffairesParMois();
        $caPotentiel = $carteRepo->getChiffreAffairesPotentielParType();
        $notesParRarete = $carteRepo->getDistributionNotesParRarete();

        $nbCartes = $this->dm->getRepository(Carte::class)->count([]);
        $nbCommandesEnAttente = $commandeRepo->countByStatut('En attente');

        return $this->render('admin/dashboard.html.twig', [
            'statsCommandes' => $statsCommandes,
            'statsTypes' => $statsTypes,
            'caParMois' => $caParMois,
            'caPotentiel' => $caPotentiel,
            'notesParRarete' => $notesParRarete,
            'nbCartes' => $nbCartes,
            'nbCommandesEnAttente' => $nbCommandesEnAttente,
        ]);
    }
}
