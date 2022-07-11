<?php

/*
 * Classe - FormatAchatCarteRepository
 *
 * Requêtes à la base de données pour l'entité Format d'achat de carte
*/

namespace App\Repository;

use App\Entity\Uca\FormatAchatCarte;
use Doctrine\Persistence\ManagerRegistry;

class FormatAchatCarteRepository extends FormatActiviteRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormatAchatCarte::class);
    }

    public function findFinishedFormats()
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.dateFinEffective <= :now')
            ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }
}
