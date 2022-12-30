<?php

namespace App\Repository;

use App\Entity\GeoPostalCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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

    /**
     * @return GeoPostalCode[]
     */
    public function search(string $countryCode, string $searchText): array
    {
        $searchPattern = str_replace(' ', '', $searchText);

        $sql = "
            SELECT
                geo_postal_code.*
            FROM
                geo_postal_code
            WHERE
                country_code = :countryCode
            AND
                replace(postal_code, ' ', '') like (:searchPattern || '%')
            LIMIT 10
            ;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(GeoPostalCode::class, 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'country_code', 'countryCode');
        $rsm->addFieldResult('p', 'postal_code', 'postalCode');
        $rsm->addFieldResult('p', 'place_name', 'placeName');
        $rsm->addFieldResult('p', 'state', 'state');
        $rsm->addFieldResult('p', 'latitude', 'latitude');
        $rsm->addFieldResult('p', 'longitude', 'longitude');
        $rsm->addFieldResult('p', 'dataset_version', 'datasetVersion');
        $rsm->addFieldResult('p', 'location', 'location');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('countryCode', $countryCode);
        $query->setParameter('searchPattern', $searchPattern);
        return $query->getResult();
    }
}
