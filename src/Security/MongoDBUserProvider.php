<?php

namespace App\Security;

use App\Document\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MongoDBUserProvider implements UserProviderInterface
{
    private UtilisateurRepository $repository;

    public function __construct(private DocumentManager $dm)
    {
        $this->repository = new UtilisateurRepository($dm);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->repository->findByEmail($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('Utilisateur "%s" introuvable.', $identifier));
        }

        if (!$user->isActif()) {
            throw new UserNotFoundException('Compte désactivé.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Type "%s" non supporté.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === Utilisateur::class || is_subclass_of($class, Utilisateur::class);
    }
}
