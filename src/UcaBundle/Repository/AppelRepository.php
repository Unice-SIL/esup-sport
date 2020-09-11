<?php

/*
 * Classe - AppelRepository
 *
 * Contient les requêtes à la base de données pour l'entité appel
*/

namespace UcaBundle\Repository;

class AppelRepository extends \Doctrine\ORM\EntityRepository
{
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
