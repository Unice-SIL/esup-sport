<?php

namespace UcaBundle\Repository;

class GroupeRepository extends \Doctrine\ORM\EntityRepository
{
    public function findGroupeEncadrant()
    {
        $qb = $this->createQueryBuilder('g')
            ->where("g.libelle = 'Encadrant'")
        ;

        return $qb->getQuery()->getResult();
    }
}
