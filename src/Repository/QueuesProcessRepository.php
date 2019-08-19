<?php

namespace App\Repository;

use App\Entity\QueuesProcess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QueuesProcess|null find($id, $lockMode = null, $lockVersion = null)
 * @method QueuesProcess|null findOneBy(array $criteria, array $orderBy = null)
 * @method QueuesProcess[]    findAll()
 * @method QueuesProcess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueuesProcessRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QueuesProcess::class);
    }

    // /**
    //  * @return QueuesProcess[] Returns an array of QueuesProcess objects
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
    public function findOneBySomeField($value): ?QueuesProcess
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
