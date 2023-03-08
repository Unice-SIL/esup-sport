<?php

/*
 * Classe - LogoParametrableRepository
 *
 * Requêtes à la base de données pour l'entité Logo Parametrable
*/

namespace App\Repository;

use App\Entity\Uca\LogoParametrable;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class LogoParametrableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogoParametrable::class);
    }
    
    public function max($field)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('MAX(a.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }
}
