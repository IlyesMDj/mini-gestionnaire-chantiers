<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Chantier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ChantierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chantier::class);
    }

    /** @return Chantier[] */
    public function findAllWithEquipements(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.equipements', 'e')
            // Sans addSelect, la jointure filtre sans hydrater : Doctrine relancerait une requête par chantier.
            ->addSelect('e')
            ->orderBy('c.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
