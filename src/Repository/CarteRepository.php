<?php

namespace App\Repository;

use App\Document\Carte;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Repository Pattern : encapsule toute la logique d'accès aux données des cartes.
 */
class CarteRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Carte::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    public function findWithFilters(array $filters = [], string $sort = 'nom', string $order = 'asc', int $page = 1, int $limit = 12): array
    {
        $qb = $this->createQueryBuilder();

        if (!empty($filters['search'])) {
            $qb->addAnd(
                $qb->expr()->field('nom')->equals(new \MongoDB\BSON\Regex($filters['search'], 'i'))
            );
        }

        if (!empty($filters['type'])) {
            $qb->field('typePokemon')->equals($filters['type']);
        }

        if (!empty($filters['rarete'])) {
            $qb->field('rarete')->equals($filters['rarete']);
        }

        if (!empty($filters['serie'])) {
            $qb->field('serie.$id')->equals(new \MongoDB\BSON\ObjectId($filters['serie']));
        }

        if (!empty($filters['prix_min'])) {
            $qb->field('prix')->gte((float) $filters['prix_min']);
        }

        if (!empty($filters['prix_max'])) {
            $qb->field('prix')->lte((float) $filters['prix_max']);
        }

        if (!empty($filters['en_stock'])) {
            $qb->field('stock')->gt(0);
        }

        $qb->sort($sort, $order === 'desc' ? -1 : 1);

        $total = (int) (clone $qb)->count()->getQuery()->execute();

        $qb->skip(($page - 1) * $limit)->limit($limit);

        return [
            'cartes' => $qb->getQuery()->execute()->toArray(),
            'total' => $total,
            'pages' => (int) ceil($total / $limit),
            'page' => $page,
        ];
    }

    public function findByRarete(string $rarete): array
    {
        return $this->createQueryBuilder()
            ->field('rarete')->equals($rarete)
            ->sort('prix', 1)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /** Agrégation MongoDB : statistiques par type */
    public function getStatistiquesParType(): array
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection(Carte::class);

        $pipeline = [
            ['$group' => [
                '_id' => '$typePokemon',
                'count' => ['$sum' => 1],
                'prixMoyen' => ['$avg' => '$prix'],
                'stockTotal' => ['$sum' => '$stock'],
            ]],
            ['$sort' => ['count' => -1]],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    /** Agrégation MongoDB : top cartes les plus chères par série */
    public function getTopCartesByRarete(): array
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection(Carte::class);

        $pipeline = [
            ['$match' => ['stock' => ['$gt' => 0]]],
            ['$group' => [
                '_id' => '$rarete',
                'count' => ['$sum' => 1],
                'prixMax' => ['$max' => '$prix'],
                'prixMin' => ['$min' => '$prix'],
                'prixMoyen' => ['$avg' => '$prix'],
                'stockTotal' => ['$sum' => '$stock'],
            ]],
            ['$sort' => ['prixMoyen' => -1]],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    /** Map Reduce : calcul du chiffre d'affaires potentiel par type */
    public function getChiffreAffairesPotentielParType(): array
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection(Carte::class);

        $pipeline = [
            ['$project' => [
                'typePokemon' => 1,
                'caPotentiel' => ['$multiply' => ['$prix', '$stock']],
                'stock' => 1,
                'prix' => 1,
            ]],
            ['$group' => [
                '_id' => '$typePokemon',
                'caTotalPotentiel' => ['$sum' => '$caPotentiel'],
                'nbCartes' => ['$sum' => 1],
                'stockTotal' => ['$sum' => '$stock'],
            ]],
            ['$sort' => ['caTotalPotentiel' => -1]],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    /** Map Reduce : distribution des notes par rareté */
    public function getDistributionNotesParRarete(): array
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection(Carte::class);

        $pipeline = [
            ['$unwind' => ['path' => '$avis', 'preserveNullAndEmptyArrays' => true]],
            ['$group' => [
                '_id' => '$rarete',
                'notesMoyennes' => ['$avg' => '$avis.note'],
                'nbAvis' => ['$sum' => ['$cond' => [['$ifNull' => ['$avis', false]], 1, 0]]],
                'nbCartes' => ['$addToSet' => '$_id'],
            ]],
            ['$project' => [
                'rarete' => '$_id',
                'notesMoyennes' => ['$round' => ['$notesMoyennes', 1]],
                'nbAvis' => 1,
                'nbCartes' => ['$size' => '$nbCartes'],
            ]],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    public function findCartesEnStock(int $limit = 8): array
    {
        return $this->createQueryBuilder()
            ->field('stock')->gt(0)
            ->sort('createdAt', -1)
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function searchByNom(string $query): array
    {
        return $this->createQueryBuilder()
            ->field('nom')->equals(new \MongoDB\BSON\Regex($query, 'i'))
            ->limit(10)
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
