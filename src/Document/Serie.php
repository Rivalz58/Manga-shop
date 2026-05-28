<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(collection: 'series', repositoryClass: \App\Repository\SerieRepository::class)]
#[ODM\Index(keys: ['nom' => 'asc'])]
class Serie
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le nom de la série est obligatoire.')]
    #[Assert\Length(max: 150)]
    private string $nom = '';

    #[ODM\Field(type: 'int')]
    #[Assert\Range(min: 1996, max: 2030)]
    private int $annee = 2024;

    #[ODM\Field(type: 'string')]
    private string $description = '';

    #[ODM\Field(type: 'string')]
    private string $image = 'default-serie.png';

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getAnnee(): int { return $this->annee; }
    public function setAnnee(int $annee): static { $this->annee = $annee; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description ?? ''; return $this; }
    public function getImage(): string { return $this->image; }
    public function setImage(string $image): static { $this->image = $image; return $this; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
}
