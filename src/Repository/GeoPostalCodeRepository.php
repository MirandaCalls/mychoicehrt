<?php

namespace App\Repository;

use App\Entity\GeoPostalCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeoPostalCode>
 *
 * @method GeoPostalCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoPostalCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoPostalCode[]    findAll()
 * @method GeoPostalCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoPostalCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoPostalCode::class);
    }

    public function save(GeoPostalCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GeoPostalCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
