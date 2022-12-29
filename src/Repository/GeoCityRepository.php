<?php

namespace App\Repository;

use App\Entity\GeoCity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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

    /**
     * @return GeoCity[]
     */
    public function search(string $searchText): array
    {
        $queryTerms = explode(' ', $searchText);
        $queryTerms = array_filter($queryTerms, function($val) {
            return $val !== '';
        });
        $queryTerms = implode(' & ', $queryTerms);

        $sql = "
            SELECT 
                geo_city.*
            FROM 
                geo_city,
                to_tsquery(:queryTerms) query,
                similarity(:searchText, geo_city.name || ' ' || geo_city.state) similarity,
                to_tsvector('simple', geo_city.alternate_names) otherNames
            WHERE
                similarity > 0 OR query @@ otherNames
            ORDER BY similarity DESC NULLS LAST
            LIMIT 10
            ;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(GeoCity::class, 'c');
        $rsm->addFieldResult('c', 'id', 'id');
        $rsm->addFieldResult('c', 'name', 'name');
        $rsm->addFieldResult('c', 'ascii_name', 'asciiName');
        $rsm->addFieldResult('c', 'alternate_names', 'alternateNames');
        $rsm->addFieldResult('c', 'latitude', 'latitude');
        $rsm->addFieldResult('c', 'longitude', 'longitude');
        $rsm->addFieldResult('c', 'state', 'state');
        $rsm->addFieldResult('c', 'country_code', 'countryCode');
        $rsm->addFieldResult('c', 'dataset_version', 'datasetVersion');
        $rsm->addFieldResult('c', 'location', 'location');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('queryTerms', $queryTerms);
        $query->setParameter('searchText', $searchText);
        return $query->getResult();
    }

}
