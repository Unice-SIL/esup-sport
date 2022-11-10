<?php

/*
 * Classe - AppelRepository
 *
 * Contient les requêtes à la base de données pour l'entité appel
*/

namespace App\Repository;

use App\Entity\Uca\Appel;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class AppelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appel::class);
    }

    public function findAppelByUserAndSerie($user, $serie)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.utilisateur = :user')
            ->setParameter('user', $user)
            ->leftJoin('a.dhtmlxEvenement', 'd')
            ->leftJoin('d.serie', 's')
            ->andWhere('s.id = :serie')
            ->setParameter('serie', $serie)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getResult();
    }
}
