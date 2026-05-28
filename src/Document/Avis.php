<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\EmbeddedDocument]
class Avis
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private string $auteurNom = '';

    #[ODM\Field(type: 'string')]
    private string $auteurEmail = '';

    #[ODM\Field(type: 'int')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note doit être entre 1 et 5.')]
    private int $note = 5;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le commentaire est obligatoire.')]
    #[Assert\Length(min: 10, max: 500)]
    private string $commentaire = '';

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }
    public function getAuteurNom(): string { return $this->auteurNom; }
    public function setAuteurNom(string $nom): static { $this->auteurNom = $nom; return $this; }
    public function getAuteurEmail(): string { return $this->auteurEmail; }
    public function setAuteurEmail(string $email): static { $this->auteurEmail = $email; return $this; }
    public function getNote(): int { return $this->note; }
    public function setNote(int $note): static { $this->note = $note; return $this; }
    public function getCommentaire(): string { return $this->commentaire; }
    public function setCommentaire(string $commentaire): static { $this->commentaire = $commentaire; return $this; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
}
