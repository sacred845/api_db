<?php

namespace App\Repository;

use App\Entity\OutputFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OutputFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method OutputFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method OutputFile[]    findAll()
 * @method OutputFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OutputFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OutputFile::class);
    }

    // /**
    //  * @return OutputFile[] Returns an array of OutputFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OutputFile
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
