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
    public const NEARBY_CLINICS_RADIUS = 0.5;
    public const METERS_PER_MILE = 1609.344;

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

    public function findClinicsWithinRadius(float $centerLat, float $centerLong, float $miles) {
        return $this->createQueryBuilder('c')
            ->andWhere('ST_distance(c.location, ST_MakePoint(:originLong, :originLat)) <= :radius')
            ->setParameter('originLong', $centerLong)
            ->setParameter('originLat', $centerLat)
            ->setParameter('radius', $miles * self::METERS_PER_MILE)
            ->getQuery()
            ->getResult();
    }

    public function findClinicsNearby(Clinic $to): array
    {
        return $this->findClinicsWithinRadius(
            $to->getLatitude(), $to->getLongitude(), self::NEARBY_CLINICS_RADIUS
        );
    }

    public function countClinics(bool $recent = false, ?bool $published = null): Int
    {
        $query = $this->createQueryBuilder('c')
            ->select('count(c.id)');

        if ($recent) {
            $query->andWhere('c.importedOn >= :sevenDaysPast')
                ->setParameter('sevenDaysPast', (new \DateTime())->sub(new \DateInterval('P7D')))
            ;
        }

        if ($published !== null) {
            $query->andWhere('c.published = :published')
                ->setParameter('published', $published)
            ;
        }

        return $query
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
