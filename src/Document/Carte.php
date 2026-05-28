<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(collection: 'cartes')]
#[ODM\Index(keys: ['nom' => 'text', 'typePokemon' => 'text'], name: 'search_index')]
#[ODM\Index(keys: ['rarete' => 'asc', 'prix' => 'asc'])]
class Carte
{
    const RARETES = ['Commune', 'Peu commune', 'Rare', 'Ultra Rare', 'Secrète'];
    const TYPES = ['Feu', 'Eau', 'Plante', 'Électrique', 'Psychique', 'Combat', 'Normal', 'Dragon', 'Acier', 'Fée', 'Obscurité', 'Incolore'];

    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le nom de la carte est obligatoire.')]
    #[Assert\Length(min: 2, max: 100)]
    private string $nom = '';

    #[ODM\ReferenceOne(targetDocument: Serie::class, cascade: ['persist'])]
    #[Assert\NotNull(message: 'La série est obligatoire.')]
    private ?Serie $serie = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::TYPES)]
    private string $typePokemon = 'Normal';

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: self::RARETES)]
    private string $rarete = 'Commune';

    #[ODM\Field(type: 'float')]
    #[Assert\Positive(message: 'Le prix doit être positif.')]
    #[Assert\NotNull]
    private float $prix = 0.0;

    #[ODM\Field(type: 'int')]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif.')]
    private int $stock = 0;

    #[ODM\Field(type: 'string')]
    #[Assert\Length(max: 1000)]
    private string $description = '';

    #[ODM\Field(type: 'string')]
    private string $image = 'default-card.png';

    #[ODM\Field(type: 'int')]
    private int $numeroPokedex = 0;

    #[ODM\Field(type: 'int')]
    private int $pv = 0;

    #[ODM\EmbedMany(targetDocument: Avis::class)]
    private Collection $avis;

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->avis = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getSerie(): ?Serie { return $this->serie; }
    public function setSerie(?Serie $serie): static { $this->serie = $serie; return $this; }

    public function getTypePokemon(): string { return $this->typePokemon; }
    public function setTypePokemon(string $type): static { $this->typePokemon = $type; return $this; }

    public function getRarete(): string { return $this->rarete; }
    public function setRarete(string $rarete): static { $this->rarete = $rarete; return $this; }

    public function getPrix(): float { return $this->prix; }
    public function setPrix(float $prix): static { $this->prix = $prix; return $this; }

    public function getStock(): int { return $this->stock; }
    public function setStock(int $stock): static { $this->stock = $stock; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $desc): static { $this->description = $desc; return $this; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): static { $this->image = $image; return $this; }

    public function getNumeroPokedex(): int { return $this->numeroPokedex; }
    public function setNumeroPokedex(int $num): static { $this->numeroPokedex = $num; return $this; }

    public function getPv(): int { return $this->pv; }
    public function setPv(int $pv): static { $this->pv = $pv; return $this; }

    public function getAvis(): Collection { return $this->avis; }
    public function addAvis(Avis $avis): static { $this->avis->add($avis); return $this; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    public function isEnStock(): bool { return $this->stock > 0; }

    public function getNoteMoyenne(): float
    {
        if ($this->avis->isEmpty()) return 0;
        $total = array_sum(array_map(fn(Avis $a) => $a->getNote(), $this->avis->toArray()));
        return round($total / $this->avis->count(), 1);
    }
}
