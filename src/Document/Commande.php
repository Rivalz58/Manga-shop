<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(collection: 'commandes', repositoryClass: \App\Repository\CommandeRepository::class)]
#[ODM\Index(keys: ['createdAt' => 'desc'])]
#[ODM\Index(keys: ['statut' => 'asc'])]
class Commande
{
    const STATUTS = ['En attente', 'Confirmée', 'En préparation', 'Expédiée', 'Livrée', 'Annulée'];

    #[ODM\Id]
    private ?string $id = null;

    #[ODM\ReferenceOne(targetDocument: Utilisateur::class)]
    private ?Utilisateur $utilisateur = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    private string $clientNom = '';

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $clientEmail = '';

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: "L'adresse de livraison est obligatoire.")]
    private string $adresseLivraison = '';

    #[ODM\EmbedMany(targetDocument: LigneCommande::class)]
    private Collection $lignes;

    #[ODM\Field(type: 'float')]
    private float $total = 0.0;

    #[ODM\Field(type: 'string')]
    private string $statut = 'En attente';

    #[ODM\Field(type: 'string')]
    private string $reference = '';

    #[ODM\Field(type: 'string')]
    private string $noteLivraison = '';

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->reference = 'CMD-' . strtoupper(substr(uniqid(), -8));
    }

    public function getId(): ?string { return $this->id; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $u): static { $this->utilisateur = $u; return $this; }

    public function getClientNom(): string { return $this->clientNom; }
    public function setClientNom(string $nom): static { $this->clientNom = $nom; return $this; }

    public function getClientEmail(): string { return $this->clientEmail; }
    public function setClientEmail(string $email): static { $this->clientEmail = $email; return $this; }

    public function getAdresseLivraison(): string { return $this->adresseLivraison; }
    public function setAdresseLivraison(string $adresse): static { $this->adresseLivraison = $adresse; return $this; }

    public function getLignes(): Collection { return $this->lignes; }
    public function addLigne(LigneCommande $ligne): static { $this->lignes->add($ligne); return $this; }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $total): static { $this->total = $total; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getReference(): string { return $this->reference; }

    public function getNoteLivraison(): string { return $this->noteLivraison; }
    public function setNoteLivraison(string $note): static { $this->noteLivraison = $note; return $this; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    public function calculerTotal(): void
    {
        $this->total = array_sum(
            array_map(fn(LigneCommande $l) => $l->getSousTotal(), $this->lignes->toArray())
        );
    }

    public function getNombreLignes(): int { return $this->lignes->count(); }
}
