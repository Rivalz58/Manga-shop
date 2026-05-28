<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(collection: 'utilisateurs', repositoryClass: \App\Repository\UtilisateurRepository::class)]
#[ODM\Index(keys: ['email' => 'asc'], options: ['unique' => true])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Email(message: 'Adresse email invalide.')]
    private string $email = '';

    #[ODM\Field(type: 'string')]
    private string $password = '';

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private string $prenom = '';

    #[ODM\Field(type: 'string')]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private string $nom = '';

    #[ODM\Field(type: 'string')]
    private string $adresse = '';

    #[ODM\Field(type: 'string')]
    private string $telephone = '';

    #[ODM\Field(type: 'collection')]
    private array $roles = ['ROLE_USER'];

    #[ODM\Field(type: 'bool')]
    private bool $actif = true;

    #[ODM\Field(type: 'date')]
    private \DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return $this->email; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getNomComplet(): string { return $this->prenom . ' ' . $this->nom; }

    public function getAdresse(): string { return $this->adresse; }
    public function setAdresse(string $adresse): static { $this->adresse = $adresse; return $this; }

    public function getTelephone(): string { return $this->telephone; }
    public function setTelephone(string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }
    public function isAdmin(): bool { return in_array('ROLE_ADMIN', $this->roles); }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    public function eraseCredentials(): void {}
}
