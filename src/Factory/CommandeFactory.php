<?php

namespace App\Factory;

use App\Document\Carte;
use App\Document\Commande;
use App\Document\LigneCommande;
use App\Document\Utilisateur;
use App\Service\CartService;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Factory Pattern : création d'une Commande à partir du panier en session.
 */
class CommandeFactory
{
    public function __construct(
        private DocumentManager $dm,
        private CartService $cartService,
    ) {}

    public function creerDepuisPanier(
        string $clientNom,
        string $clientEmail,
        string $adresseLivraison,
        ?Utilisateur $utilisateur = null
    ): Commande {
        $commande = new Commande();
        $commande->setClientNom($clientNom);
        $commande->setClientEmail($clientEmail);
        $commande->setAdresseLivraison($adresseLivraison);

        if ($utilisateur) {
            $commande->setUtilisateur($utilisateur);
        }

        $panierDetails = $this->cartService->getPanierAvecDetails();

        foreach ($panierDetails['items'] as $item) {
            $ligne = new LigneCommande();
            $ligne->setCarte($item['carte']);
            $ligne->setQuantite($item['quantite']);
            $ligne->setPrixUnitaire($item['carte']->getPrix());
            $commande->addLigne($ligne);
        }

        $commande->setTotal($panierDetails['total']);

        return $commande;
    }
}
