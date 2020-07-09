<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;

class CommandeDetailRepository extends \Doctrine\ORM\EntityRepository
{
    public function criteriaByAutorisation($autorisation)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('typeAutorisation', $autorisation))
        ;
    }

    public function findCommandeDetails($dateDebut, $dateFin, $montantPaye)
    {
        $qb = $this->createQueryBuilder('cd');
        $qb->leftJoin('UcaBundle\Entity\Commande', 'c', 'WITH', 'c.id = cd.commande');
        $qb->where('c.datePaiement IS NOT NULL');

        if (null != $dateDebut and null != $dateFin) {
            $qb->add(
                'where',
                $qb->expr()->between(
                    'c.datePaiement',
                    ':from',
                    ':to'
                )
            )
                ->setParameters(['from' => $dateDebut, 'to' => $dateFin])
                    ;
        } elseif (null != $dateDebut and null == $dateFin) {
            $qb->where('c.datePaiement >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut)
            ;
        } elseif (null == $dateDebut and null != $dateFin) {
            $qb->where('c.datePaiement <= :dateFin')
                ->setParameter('dateFin', $dateFin)
            ;
        }

        if ($montantPaye) {
            $qb->andWhere('cd.montant > 0');
        }

        return $qb->getQuery()->getResult();
    }

    public function findCommandeDetailPourAncienneCommandeGratuite()
    {
        $qb = $this->createQueryBuilder('cd');
        $qb->leftJoin('UcaBundle\Entity\Commande', 'c', 'WITH', 'c.id = cd.commande');
        $qb->where("c.statut = 'termine'");
        $qb->andWhere('cd.libelle IS NULL OR cd.description IS NULL OR cd.typeArticle IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function max($field)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('MAX(c.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }
}
