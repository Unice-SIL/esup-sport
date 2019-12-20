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

    public function findCommandeDetails($date_debut = null, $date_fin = null, $montantPaye = null)
    {
        $dateDebut = \DateTime::createFromFormat('d-m-Y', $date_debut);
        $dateFin = \DateTime::createFromFormat('d-m-Y', $date_fin);

        if ('null' == $date_debut) {
            $date_debut = null;
        }
        if ('null' == $date_fin) {
            $date_fin = null;
        }
        $qb = $this->createQueryBuilder('cd');
        $qb->leftJoin('UcaBundle\Entity\Commande', 'c', 'WITH', 'c.id = cd.commande');
        $qb->where('c.datePaiement IS NOT NULL');

        if (null != $date_debut and null != $date_fin) {
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
        } elseif (null != $date_debut and null == $date_fin) {
            $qb->where('c.datePaiement > :dateDebut')
                ->setParameter('dateDebut', $dateDebut)
            ;
        } elseif (null == $date_debut and null != $date_fin) {
            $qb->where('c.datePaiement < :dateFin')
                ->setParameter('dateFin', $dateFin)
            ;
        }

        if ($montantPaye) {
            $qb->andWhere('cd.montant > 0');
        }

        return $qb->getQuery()->getResult();
    }
}
