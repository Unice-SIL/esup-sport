<?php

/*
 * Classe - DhtmlxSerieRepository
 *
 * Contient les requêtes à la base de données pour l'entité DhtmlxSerie
*/

namespace UcaBundle\Repository;

class DhtmlxSerieRepository extends \Doctrine\ORM\EntityRepository
{
    public function findDhtmlxDateByReference($type, $id)
    {
        $query = $this->createQueryBuilder('d');
        if ('ressource' == $type) {
            $query->join('d.evenements', 'e')
                ->join('d.reservabilite', 're')
                ->join('re.ressource', 'r')
                ->where('r.id = :id')
                ->addSelect('e, re')
            ;
        } elseif ('FormatActivite' == $type) {
            $query->join('d.creneau', 'c')
                ->join('c.formatActivite', 'f')
                ->where('f.id = :id')
                ->addSelect('c')
            ;
        }
        $query->setParameter('id', $id);

        return $query->getQuery()->getResult();
    }

    public function findDhtmlxDateByReferenceOld($type, $id)
    {
        $query = $this->createQueryBuilder('d');
        if ('ressource' == $type) {
            $query->join('d.evenements', 'e')
                ->join('e.reservabilite', 're')
                ->join('re.ressource', 'r')
                ->where('r.id = :id')
                ->addSelect('e, re')
                ->setParameter('id', $id)
            ;
        }

        return $query->getQuery()->getResult();
    }

    public function findDhtmlxCreneauByUser($user)
    {
        return $this->createQueryBuilder('d')
            ->join('d.creneau', 'c')
            ->join('c.inscriptions', 'i')
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', 'valide')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDhtmlxCreneauByEncadrant($user)
    {
        return $this->createQueryBuilder('d')
            ->join('d.creneau', 'c')
            ->join('c.encadrants', 'e')
            ->andWhere('e.id = :userid')
            ->setParameter('userid', $user->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDhtmlxSerieByCreneau($id)
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.creneau = :id')
            ->setParameter('id', $id)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findSerieReservation() {
        return $this->createQueryBuilder('s')
            ->andWhere('s.reservabilite is not null and s.creneau is null')
            ->getQuery()
            ->getResult()
        ;
    }
}
