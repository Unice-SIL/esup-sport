<?php

/*
 * Classe - AutorisationRepository
 *
 * Contient les requêtes à la base de données pour l'entité autorisation
*/

namespace UcaBundle\Repository;

class AutorisationRepository extends FormatActiviteRepository
{
    public function findFinishedAutorisations()
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.inscription', 'i')
            ->leftJoin('i.formatActivite', 'fa')
            ->where('fa.dateFinEffective <= :now')
            ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }
}
