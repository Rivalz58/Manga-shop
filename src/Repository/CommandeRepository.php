<?php

namespace App\Repository;

use App\Document\Commande;
use App\Document\Utilisateur;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class CommandeRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Commande::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    public function findByUtilisateur(Utilisateur $user): array
    {
        return $this->createQueryBuilder()
            ->field('utilisateur.$id')->equals(new \MongoDB\BSON\ObjectId($user->getId()))
            ->sort('createdAt', -1)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findWithFilters(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder();

        if (!empty($filters['statut'])) {
            $qb->field('statut')->equals($filters['statut']);
        }

        if (!empty($filters['reference'])) {
            $qb->field('reference')->equals(new \MongoDB\BSON\Regex($filters['reference'], 'i'));
        }

        if (!empty($filters['email'])) {
            $qb->field('clientEmail')->equals(new \MongoDB\BSON\Regex($filters['email'], 'i'));
        }

        $qb->sort('createdAt', -1);

        $total = (int) (clone $qb)->count()->getQuery()->execute();
        $qb->skip(($page - 1) * $limit)->limit($limit);

        return [
            'commandes' => $qb->getQuery()->execute()->toArray(),
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
            'page' => $page,
        ];
    }

    /** Agrégation : chiffre d'affaires par mois */
    public function getChiffreAffairesParMois(): array
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection(Commande::class);

        $pipeline = [
            ['$match' => ['statut' => ['$nin' => ['Annulée']]]],
            ['$group' => [
                '_id' => [
                    'annee' => ['$year' => '$createdAt'],
                    'mois' => ['$month' => '$createdAt'],
                ],
                'ca' => ['$sum' => '$total'],
                'nbCommandes' => ['$sum' => 1],
                'panierMoyen' => ['$avg' => '$total'],
            ]],
            ['$sort' => ['_id.annee' => -1, '_id.mois' => -1]],
            ['$limit' => 12],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    /** Agrégation : statistiques globales */
    public function getStatistiquesGlobales(): array
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection(Commande::class);

        $pipeline = [
            ['$group' => [
                '_id' => '$statut',
                'count' => ['$sum' => 1],
                'total' => ['$sum' => '$total'],
            ]],
            ['$sort' => ['count' => -1]],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder()
            ->field('statut')->equals($statut)
            ->count()
            ->getQuery()
            ->execute();
    }
}
