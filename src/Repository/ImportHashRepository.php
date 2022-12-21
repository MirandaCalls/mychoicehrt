<?php

namespace App\Repository;

use App\Entity\ImportHash;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImportHash>
 *
 * @method ImportHash|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImportHash|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImportHash[]    findAll()
 * @method ImportHash[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportHashRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportHash::class);
    }

    public function save(ImportHash $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ImportHash $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByHash($hashValue): ?ImportHash
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.hash = :hashValue')
            ->setParameter('hashValue', $hashValue)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
