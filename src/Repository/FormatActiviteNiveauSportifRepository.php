<?php

namespace App\Repository;

use App\Entity\Uca\FormatActiviteNiveauSportif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormatActiviteNiveauSportif>
 *
 * @method FormatActiviteNiveauSportif|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormatActiviteNiveauSportif|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormatActiviteNiveauSportif[]    findAll()
 * @method FormatActiviteNiveauSportif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormatActiviteNiveauSportifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatActiviteNiveauSportif::class);
    }

    public function add(FormatActiviteNiveauSportif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FormatActiviteNiveauSportif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FormatActiviteNiveauSportif[] Returns an array of FormatActiviteNiveauSportif objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormatActiviteNiveauSportif
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
