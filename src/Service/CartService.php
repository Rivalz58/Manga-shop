<?php

namespace App\Service;

use App\Document\Carte;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service de gestion du panier en session.
 * Stratégie : panier stocké en session côté serveur.
 */
class CartService
{
    private const SESSION_KEY = 'panier';

    public function __construct(
        private RequestStack $requestStack,
        private DocumentManager $dm,
    ) {}

    private function getSession(): \Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function getContenu(): array
    {
        return $this->getSession()->get(self::SESSION_KEY, []);
    }

    public function ajouterCarte(string $carteId, int $quantite = 1): void
    {
        $panier = $this->getContenu();
        if (isset($panier[$carteId])) {
            $panier[$carteId] += $quantite;
        } else {
            $panier[$carteId] = $quantite;
        }
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    public function retirerCarte(string $carteId): void
    {
        $panier = $this->getContenu();
        unset($panier[$carteId]);
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    public function modifierQuantite(string $carteId, int $quantite): void
    {
        if ($quantite <= 0) {
            $this->retirerCarte($carteId);
            return;
        }
        $panier = $this->getContenu();
        $panier[$carteId] = $quantite;
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    public function vider(): void
    {
        $this->getSession()->remove(self::SESSION_KEY);
    }

    public function getNombreArticles(): int
    {
        return array_sum($this->getContenu());
    }

    public function getPanierAvecDetails(): array
    {
        $panier = $this->getContenu();
        $items = [];
        $total = 0.0;

        foreach ($panier as $carteId => $quantite) {
            $carte = $this->dm->find(Carte::class, $carteId);
            if ($carte) {
                $sousTotal = $carte->getPrix() * $quantite;
                $items[] = [
                    'carte' => $carte,
                    'quantite' => $quantite,
                    'sousTotal' => $sousTotal,
                ];
                $total += $sousTotal;
            }
        }

        return ['items' => $items, 'total' => $total];
    }
}
