<?php

namespace App\Repository;

use App\Entity\Clinic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clinic>
 *
 * @method Clinic|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clinic|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clinic[]    findAll()
 * @method Clinic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClinicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clinic::class);
    }

    public function save(Clinic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Clinic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Clinic[]
     */
    public function filterAllWithCallable(callable $filterFunc): array
    {
        $iterable = $this->createQueryBuilder('c')
            ->getQuery()
            ->toIterable();

        $filtered = [];
        foreach ($iterable as $clinic) {
            if ($filterFunc($clinic)) {
                $filtered[] = $clinic;
            }

            $this->getEntityManager()->detach($clinic);
        }

        return $filtered;
    }

}
