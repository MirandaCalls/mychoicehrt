<?php

namespace App\Repository;

use App\Entity\FeedbackMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedbackMessage>
 *
 * @method FeedbackMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackMessage[]    findAll()
 * @method FeedbackMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackMessage::class);
    }

    public function save(FeedbackMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FeedbackMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
