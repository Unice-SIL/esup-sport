<?php

namespace UcaBundle\Repository;

use Doctrine\ORM\Query\Expr\Join;

class EtablissementRepository extends \Doctrine\ORM\EntityRepository
{
    public function findEtablissementByActivite($idActivite)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $idActivite)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByCreneau($id)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->join('UcaBundle\Entity\Creneau', 'c', Join::WITH, 'l = c.lieu')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByFormatActivite($id)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->where('f.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByClasseActivite($id)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->leftJoin('a.classeActivite', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByTypeActivite($id)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->leftJoin('f.activite', 'a')
            ->leftJoin('a.classeActivite', 'c')
            ->leftJoin('c.typeActivite', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByInscription($id)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->join('UcaBundle\Entity\Inscription', 'i', Join::WITH, 'f = i.formatActivite')
            ->where('i.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findEtablissementByEncadrant($id)
    {
        return $this->createQueryBuilder('e')
            ->join('UcaBundle\Entity\Lieu', 'l', Join::WITH, 'e = l.etablissement')
            ->leftJoin('l.formatsActivite', 'f')
            ->join('UcaBundle\Entity\Utilisateur', 'u', Join::WITH, 'f = u.formatsActivite')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;
    }
}
