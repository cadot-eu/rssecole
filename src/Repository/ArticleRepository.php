<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    //function pour retrouver les articles ou la priorité n'est pas 0
    public function findByPrioriteNonFinis(): array
    {
        return $this->createQueryBuilder('a')
            ->where('NOT EXISTS (
            SELECT 1 FROM App\Entity\Marque m1 WHERE m1.article = a AND m1.etat = true
        ) OR EXISTS (
            SELECT 1 FROM App\Entity\Marque m2 WHERE m2.article = a AND m2.etat = false
        ) OR EXISTS (
            SELECT 1 FROM App\Entity\Question q1 WHERE q1.article = a AND q1.etat = false
        )')
            ->andWhere('a.priorite > 0')
            ->andWhere("a.etat <> 'lu'")
            ->orderBy('a.priorite', 'ASC')
            ->addOrderBy('a.lecturemn', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findByPrioriteFinis(): array
    {
        return $this->createQueryBuilder('a')
            ->where('
            EXISTS (
            SELECT 1 FROM App\Entity\Marque m1 WHERE m1.article = a AND m1.etat = true
        ) AND
            EXISTS (
            SELECT 1 FROM App\Entity\Question q1 WHERE q1.article = a AND q1.etat = true
        )')
            ->orWhere("a.etat = 'lu'")
            ->andWhere('a.priorite > 0')
            ->orderBy('a.priorite', 'ASC')
            ->addOrderBy('a.lecturemn', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
