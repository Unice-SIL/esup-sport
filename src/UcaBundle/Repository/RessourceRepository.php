<?php

/*
 * Classe - RessourceRepository

 * Requêtes à la base de données pour l'entité ressource
*/

namespace UcaBundle\Repository;

class RessourceRepository extends \Doctrine\ORM\EntityRepository
{
    public function findDhtmlxDateByRessourceId($id)
    {
        $data = [];

        $repository = $this
            ->getEntityManager()
            ->getRepository('UcaBundle:DhtmlxEvenement')
        ;

        $data = $repository->createQueryBuilder('e')
            ->leftJoin('e.reservabilite', 'r')
            ->leftJoin('r.ressource', 'ress')
            ->where('ress.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
        ;

        $repository = $this
            ->getEntityManager()
            ->getRepository('UcaBundle:DhtmlxSerie')
        ;

        $series = [];

        //get series
        foreach ($data as $key => $d) {
            if (null !== $d->getSerie()) {
                array_push($data, $d->getSerie());
            }
        }

        return $data;
    }

    public function findAllLieu()
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.etablissement IS NOT NULL')
        ;

        return $qb->getQuery()->getResult();
    }
}
