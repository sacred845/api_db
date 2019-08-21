<?php

namespace App\Repository;

use App\Entity\FilingFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FilingFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method FilingFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method FilingFile[]    findAll()
 * @method FilingFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilingFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FilingFile::class);
    }

    // /**
    //  * @return FilingFile[] Returns an array of FilingFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FilingFile
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
