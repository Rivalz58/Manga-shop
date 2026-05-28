<?php

namespace App\Controller;

use App\Document\Utilisateur;
use App\Factory\CommandeFactory;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Service\CartService;
use App\Service\StockService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    public function __construct(
        private DocumentManager $dm,
        private CartService $cartService,
        private CommandeFactory $commandeFactory,
        private StockService $stockService,
    ) {}

    #[Route('/recap', name: 'app_commande_recap')]
    public function recap(): Response
    {
        $panierDetails = $this->cartService->getPanierAvecDetails();

        if (empty($panierDetails['items'])) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        return $this->render('commande/recap.html.twig', [
            'items' => $panierDetails['items'],
            'total' => $panierDetails['total'],
        ]);
    }

    #[Route('/valider', name: 'app_commande_valider')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function valider(Request $request): Response
    {
        $panierDetails = $this->cartService->getPanierAvecDetails();

        if (empty($panierDetails['items'])) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        /** @var Utilisateur $user */
        $user = $this->getUser();

        $commande = $this->commandeFactory->creerDepuisPanier(
            $user->getNomComplet(),
            $user->getEmail(),
            $user->getAdresse(),
            $user
        );

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->stockService->reserverStock($commande)) {
                $this->addFlash('error', 'Stock insuffisant pour une ou plusieurs cartes.');
                return $this->redirectToRoute('app_panier');
            }

            $this->dm->persist($commande);
            $this->dm->flush();

            $this->cartService->vider();

            return $this->redirectToRoute('app_commande_confirmation', ['id' => $commande->getId()]);
        }

        return $this->render('commande/valider.html.twig', [
            'form' => $form,
            'items' => $panierDetails['items'],
            'total' => $panierDetails['total'],
        ]);
    }

    #[Route('/confirmation/{id}', name: 'app_commande_confirmation')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function confirmation(string $id): Response
    {
        $commande = $this->dm->find(\App\Document\Commande::class, $id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/mes-commandes', name: 'app_commande_mes_commandes')]
    #[IsGranted('ROLE_USER')]
    public function mesCommandes(): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $repo = new CommandeRepository($this->dm);
        $commandes = $repo->findByUtilisateur($user);

        return $this->render('commande/mes_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}
