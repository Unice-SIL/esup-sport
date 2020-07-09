<?php

namespace UcaBundle\Repository;

class UtilisateurCreditHistoriqueRepository extends \Doctrine\ORM\EntityRepository
{
    public function findLastBascule()
    {
        $qb = $this->createQueryBuilder('credit');
        $qb->select('max(credit.date)')
            ->where('credit.operation = :operation')
            ->setParameters(['operation' => "fin d'année universitaire"])
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
