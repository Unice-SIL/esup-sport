<?php

/*
 * Classe - FormatAchatCarteRepository
 *
 * Requêtes à la base de données pour l'entité Format d'achat de carte
*/

namespace UcaBundle\Repository;

class FormatAchatCarteRepository extends FormatActiviteRepository
{
    public function findFinishedFormats()
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.dateFinEffective <= :now')
            ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }
}
