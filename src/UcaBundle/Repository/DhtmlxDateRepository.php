<?php

/*
 * Classe - DhtmlxDateRepository
 *
 * Contient les requêtes à la base de données pour l'entité DhtmlxDate
 * Enitté mère pour la libraire Dhtmlx Scheduler
*/

namespace UcaBundle\Repository;

class DhtmlxDateRepository extends \Doctrine\ORM\EntityRepository
{
    public function findDhtmlxDateByEncadrant($id)
    {
        $data = [];

        //creneau
        $repository = $this
            ->getEntityManager()
            ->getRepository('UcaBundle:DhtmlxSerie')
            ;

        $query = $repository->createQueryBuilder('d')
            ->leftJoin('d.creneau', 'c')
            ->leftJoin('c.encadrants', 'e')
            ->leftJoin('c.formatActivite', 'f')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;

        $data = [];
        foreach ($query as $key => $q) {
            $data[] = $q;

            $repository = $this
                ->getEntityManager()
                ->getRepository('UcaBundle:DhtmlxEvenement')
                ;

            $data = array_merge($data, $repository->createQueryBuilder('e')
                ->leftJoin('e.serie', 's')
                ->where('s.id = :id')
                ->setParameter('id', $q->getId())
                ->getQuery()
                ->getResult());
        }

        return $data;
    }
}
