<?php

namespace App\Repository;

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

}
