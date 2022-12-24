<?php

namespace App\Repository;

use App\Entity\Clinic;
use App\Entity\DuplicateLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DuplicateLink>
 *
 * @method DuplicateLink|null find($id, $lockMode = null, $lockVersion = null)
 * @method DuplicateLink|null findOneBy(array $criteria, array $orderBy = null)
 * @method DuplicateLink[]    findAll()
 * @method DuplicateLink[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DuplicateLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DuplicateLink::class);
    }

    public function save(DuplicateLink $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DuplicateLink $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findForClinicPair(Clinic $a, Clinic $b): ?DuplicateLink
    {
        return $this->createQueryBuilder('d')
            ->orWhere('d.clinicA = :clinicA AND d.clinicB = :clinicB')
            ->orWhere('d.clinicA = :clinicB AND d.clinicB = :clinicA')
            ->setParameters([
                'clinicA' => $a,
                'clinicB' => $b,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function countDuplicates(?bool $dismissed = false): int
    {
        $query = $this->createQueryBuilder('d')
            ->select('count(d.id)')
        ;

        if ($dismissed !== null) {
            $query
                ->andWhere('d.dismissed = :dismissed')
                ->setParameter('dismissed', $dismissed)
            ;
        }

        return $query->getQuery()
            ->getSingleScalarResult()
        ;
    }

}
