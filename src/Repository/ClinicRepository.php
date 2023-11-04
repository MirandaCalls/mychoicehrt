<?php

namespace App\Repository;

use App\Entity\Clinic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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

    public function findClinicsWithinRadius(
        float $centerLat,
        float $centerLong,
        float $miles,
        ?int $limit = null,
        ?int $offset = null,
        ?bool $published = null,
    ) {
        $sql = "
            SELECT
                clinic.*,
                distance
            FROM 
                clinic,
                ST_Distance(clinic.location, ST_MakePoint(:centerLong, :centerLat)) as distance
            WHERE
                distance <= :radius
        ";

        if ($published !== null) {
            $sql .= '
                AND
                    published = :published
            ';
        }

        $sql .= 'ORDER BY distance ';

        if ($limit !== null) {
            $sql .= 'LIMIT :limit ';
        }

        if ($offset !== null) {
            $sql .= 'OFFSET :offset ';
        }

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Clinic::class, 'c');
        $rsm->addFieldResult('c', 'id', 'id');
        $rsm->addFieldResult('c', 'data_source', 'dataSource');
        $rsm->addFieldResult('c', 'name', 'name');
        $rsm->addFieldResult('c', 'description', 'description');
        $rsm->addFieldResult('c', 'address', 'address');
        $rsm->addFieldResult('c', 'latitude', 'latitude');
        $rsm->addFieldResult('c', 'longitude', 'longitude');
        $rsm->addFieldResult('c', 'location', 'location');
        $rsm->addFieldResult('c', 'published', 'published');
        $rsm->addFieldResult('c', 'imported_on', 'importedOn');
        $rsm->addFieldResult('c', 'updated_on', 'updatedOn');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('centerLong', $centerLong);
        $query->setParameter('centerLat', $centerLat);
        $query->setParameter('radius', $miles * self::METERS_PER_MILE);
        if ($published !== null) {
            $query->setParameter('published', $published);
        }
        if ($limit !== null) {
            $query->setParameter('limit', $limit);
        }
        if ($offset !== null) {
            $query->setParameter('offset', $offset);
        }
        return $query->getResult();
    }

    public function findClinicsNearby(Clinic $to): array
    {
        return $this->findClinicsWithinRadius(
            $to->getLatitude(),
            $to->getLongitude(),
            self::NEARBY_CLINICS_RADIUS
        );
    }

    public function countClinicsWithinRadius(
        float $centerLat,
        float $centerLong,
        int $radius,
        ?bool $published = null,
    ): int {
        $query = $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('ST_Distance(c.location, ST_MakePoint(:centerLong, :centerLat)) <= :radius')
            ->setParameter('centerLong', $centerLong)
            ->setParameter('centerLat', $centerLat)
            ->setParameter('radius', $radius * self::METERS_PER_MILE)
        ;

        if ($published !== null) {
            $query
                ->andWhere('c.published = :published')
                ->setParameter('published', $published)
            ;
        }

        return $query
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countClinics(bool $recent = false, ?bool $published = null): int
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
