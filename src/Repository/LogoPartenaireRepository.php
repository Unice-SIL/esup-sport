<?php

/*
 * Classe - LogoPartenaireRepository
 *
 * Requêtes à la base de données pour l'entité Logo Partenaire
*/

namespace App\Repository;

use App\Entity\Uca\LogoPartenaire;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class LogoPartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogoPartenaire::class);
    }
    
    public function max($field)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('MAX(a.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }
}
