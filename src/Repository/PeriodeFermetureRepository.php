<?php

namespace App\Repository;

use App\Entity\Uca\PeriodeFermeture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PeriodeFermeture>
 *
 * @method PeriodeFermeture|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeriodeFermeture|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeriodeFermeture[]    findAll()
 * @method PeriodeFermeture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeriodeFermetureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeriodeFermeture::class);
    }

    public function add(PeriodeFermeture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PeriodeFermeture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PeriodeFermeture[] Returns an array of PeriodeFermeture objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PeriodeFermeture
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
