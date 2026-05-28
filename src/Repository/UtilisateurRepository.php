<?php

namespace App\Repository;

use App\Document\Utilisateur;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class UtilisateurRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Utilisateur::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findAllActifs(): array
    {
        return $this->createQueryBuilder()
            ->field('actif')->equals(true)
            ->sort('createdAt', -1)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findWithFilters(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder();

        if (!empty($filters['search'])) {
            $regex = new \MongoDB\BSON\Regex($filters['search'], 'i');
            $qb->addOr(
                $qb->expr()->field('email')->equals($regex),
                $qb->expr()->field('nom')->equals($regex),
                $qb->expr()->field('prenom')->equals($regex)
            );
        }

        if (!empty($filters['role'])) {
            $qb->field('roles')->equals($filters['role']);
        }

        $total = (clone $qb)->getQuery()->count();
        $qb->sort('createdAt', -1)->skip(($page - 1) * $limit)->limit($limit);

        return [
            'utilisateurs' => $qb->getQuery()->execute()->toArray(),
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
            'page' => $page,
        ];
    }
}
