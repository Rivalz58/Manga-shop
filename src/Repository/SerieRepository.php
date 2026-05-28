<?php

namespace App\Repository;

use App\Document\Serie;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class SerieRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Serie::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    public function findAllOrderedByAnnee(): array
    {
        return $this->createQueryBuilder()
            ->sort('annee', -1)
            ->getQuery()
            ->execute()
            ->toArray();
    }
}
