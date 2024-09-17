<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

       /**
    * @return Blog[] Retourne un tableau des X derniers articles ajoutés par le biais de leur id
    */
   public function findlastXArticles($value): array
   {
       return $this->createQueryBuilder('b')
           ->orderBy('b.id', 'DESC')
           ->setMaxResults($value)
           ->getQuery()
           ->getResult()
       ;
   }
   public function orderLastArticles(): array
   {
       return $this->createQueryBuilder('b')
           ->orderBy('b.id', 'DESC')
           ->getQuery()
           ->getResult()
       ;
   }
//    /**
//     * @return Blog[] Returns an array of Blog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Blog
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
