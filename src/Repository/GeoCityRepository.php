<?php

namespace App\Repository;

use App\Entity\GeoCity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeoCity>
 *
 * @method GeoCity|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoCity|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoCity[]    findAll()
 * @method GeoCity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoCityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoCity::class);
    }

    public function save(GeoCity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GeoCity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
