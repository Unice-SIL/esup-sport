<?php

/*
 * Classe - IntervalleDateRepository
 *
 * Requête concernant les élements DHTMLX, permet les réquêtes entre deux dates
*/

namespace App\Repository;

use Doctrine\ORM\Query\Expr\Join;

class IntervalleDateRepository extends \Doctrine\ORM\EntityRepository
{
    public function findDhtmlxEventsByReference($reference)
    {
        return $this->getDhtmlxEvents($this->findByReference($reference));
    }

    public function findByReference($reference)
    {
        // if (is_a($reference, 'UcaBundle\Entity\FormatAvecCreneau')) {
        //     return $this->findByFormatAvecCreneau($reference->getId());
        // }
        if (is_a($reference, 'App\Entity\Uca\FormatAvecCreneau')) {
            return $this->findBy(['formatAvecCreneau' => $reference]);
        }
        if (is_a($reference, 'App\Entity\Uca\Ressource')) {
            // return $this->findByRessource($options['id']);
            return $this->findBy(['ressource' => $reference]);
        }

        return $this->findAll();
    }

    public function findByFormatAvecCreneau($id)
    {
        $qb = $this->createQueryBuilder('i');
        $qb->join('i.creneau', 'c');
        $qb->join('c.formatActivite', 'f');
        $qb->andWhere('f.id = '.$id);

        return $qb->getQuery()->getResult();
    }

    // public function findByRessource($id)
    // {
    //     $qb = $this->createQueryBuilder('i');
    //     $qb->join('i.ressource', 'r');
    //     $qb->andWhere('r.id = ' . $id);
    //     return $qb->getQuery()->getResult();
    // }

    // public function findByActiviteAvecCreneau(\UcaBundle\Entity\FormatAvecCreneau $item)
    // {
    //     return $this->findBy(['creneau' => $item->getCreneaux()->toArray()]);
    // }

    public function getDhtmlxEvents(array $intervallesDate)
    {
        $res = [];
        foreach ($intervallesDate as $key => $intervalleDate) {
            $res[$key] = $intervalleDate->getDhtmlxEvent();
        }

        return $res;
    }
}
