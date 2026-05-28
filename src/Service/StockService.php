<?php

namespace App\Service;

use App\Document\Carte;
use App\Document\Commande;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Strategy Pattern : gestion du stock selon le statut de la commande.
 */
class StockService
{
    public function __construct(private DocumentManager $dm) {}

    public function reserverStock(Commande $commande): bool
    {
        foreach ($commande->getLignes() as $ligne) {
            $carte = $ligne->getCarte();
            if (!$carte || $carte->getStock() < $ligne->getQuantite()) {
                return false;
            }
        }

        foreach ($commande->getLignes() as $ligne) {
            $carte = $ligne->getCarte();
            $carte->setStock($carte->getStock() - $ligne->getQuantite());
        }

        $this->dm->flush();
        return true;
    }

    public function libererStock(Commande $commande): void
    {
        foreach ($commande->getLignes() as $ligne) {
            $carte = $ligne->getCarte();
            if ($carte) {
                $carte->setStock($carte->getStock() + $ligne->getQuantite());
            }
        }
        $this->dm->flush();
    }

    public function verifierDisponibilite(Carte $carte, int $quantite): bool
    {
        return $carte->getStock() >= $quantite;
    }
}
