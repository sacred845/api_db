<?php

namespace App\Repository;

use App\Entity\QueuesTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QueuesTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method QueuesTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method QueuesTask[]    findAll()
 * @method QueuesTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueuesTaskRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QueuesTask::class);
    }

    public function isFullTaskStack()
    {
        $qb = $this->createQueryBuilder('p')
                    ->select('count(p)')
					->andWhere('p.status = :status')
					->andWhere('p.updated_at > :started_at')
					->setParameter('started_at', new \DateTime('-1 day'))
					->setParameter('status', QueuesTask::STATUS_INPROGRESS);
            
        $query = $qb->getQuery();
        $res = $query->getSingleScalarResult();

        return $res > 50;
    } 

    // /**
    //  * @return QueuesTask[] Returns an array of QueuesTask objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QueuesTask
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
