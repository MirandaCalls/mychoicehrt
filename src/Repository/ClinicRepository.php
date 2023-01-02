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

    public function findClinicsWithinRadius(float $centerLat, float $centerLong, float $miles, ?int $limit = null) {
        $sql = "
            SELECT
                clinic.*,
                distance
            FROM 
                clinic,
                ST_Distance(clinic.location, ST_MakePoint(:originLong, :originLat)) as distance
            WHERE
                distance <= :radius
            ORDER BY distance
        ";

        if ($limit !== null) {
            $sql .= 'LIMIT :limit';
        }

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Clinic::class, 'c');
        $rsm->addFieldResult('c', 'id', 'id');
        $rsm->addFieldResult('c', 'data_source', 'dataSource');
        $rsm->addFieldResult('c', 'name', 'name');
        $rsm->addFieldResult('c', 'description', 'description');
        $rsm->addFieldResult('c', 'latitude', 'latitude');
        $rsm->addFieldResult('c', 'longitude', 'longitude');
        $rsm->addFieldResult('c', 'location', 'location');
        $rsm->addFieldResult('c', 'published', 'published');
        $rsm->addFieldResult('c', 'imported_on', 'importedOn');
        $rsm->addFieldResult('c', 'updated_on', 'updatedOn');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('originLong', $centerLong);
        $query->setParameter('originLat', $centerLat);
        $query->setParameter('radius', $miles * self::METERS_PER_MILE);
        if ($limit !== null) {
            $query->setParameter('limit', $limit);
        }
        return $query->getResult();
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
