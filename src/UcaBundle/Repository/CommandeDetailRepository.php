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
        $today = date('d-m-Y', time());
        $dateDebutTime = strtotime($date_debut);
        $dateFinTime = strtotime($date_fin);
        $timeToday = strtotime($today);
        $dateFinToday = ($dateFinTime > $timeToday || $dateFinTime == $timeToday) ? null : $dateFinTime;
        $dateDebut = \DateTime::createFromFormat('d-m-Y', $date_debut);
        $dateFin = \DateTime::createFromFormat('d-m-Y', $date_fin);

        $qb = $this->createQueryBuilder('cd');
        $qb->leftJoin('UcaBundle\Entity\Commande', 'c', 'WITH', 'c.id = cd.commande');
        $qb->where('c.datePaiement IS NOT NULL');

        if (null != $dateDebutTime and null != $dateFinToday) {
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
        } elseif (null != $dateDebutTime and null == $dateFinToday) {
            $qb->where('c.datePaiement > :dateDebut')
                ->setParameter('dateDebut', $dateDebut)
            ;
        } elseif (null == $dateDebutTime and null != $dateFinToday) {
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
