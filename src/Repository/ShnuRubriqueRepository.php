<?php

namespace App\Repository;

use App\Entity\Uca\ShnuRubrique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShnuRubriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShnuRubrique::class);
    }

    public function max($field)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('MAX(r.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }
}
