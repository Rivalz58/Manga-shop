<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\EmbeddedDocument]
class LigneCommande
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\ReferenceOne(targetDocument: Carte::class)]
    private ?Carte $carte = null;

    #[ODM\Field(type: 'string')]
    private string $carteNom = '';

    #[ODM\Field(type: 'string')]
    private string $carteImage = '';

    #[ODM\Field(type: 'int')]
    private int $quantite = 1;

    #[ODM\Field(type: 'float')]
    private float $prixUnitaire = 0.0;

    public function getId(): ?string { return $this->id; }

    public function getCarte(): ?Carte { return $this->carte; }
    public function setCarte(?Carte $carte): static
    {
        $this->carte = $carte;
        if ($carte) {
            $this->carteNom = $carte->getNom();
            $this->carteImage = $carte->getImage();
        }
        return $this;
    }

    public function getCarteNom(): string { return $this->carteNom; }
    public function getCarteImage(): string { return $this->carteImage; }

    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $prix): static { $this->prixUnitaire = $prix; return $this; }

    public function getSousTotal(): float { return $this->prixUnitaire * $this->quantite; }
}
