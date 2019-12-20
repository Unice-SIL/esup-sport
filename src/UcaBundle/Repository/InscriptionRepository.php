<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use UcaBundle\Service\Common\Parametrage;

class InscriptionRepository extends \Doctrine\ORM\EntityRepository
{
    public static function criteriaANettoyer()
    {
        $eb = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $eb->orX(
                $eb->andX(
                    $eb->eq('statut', 'attenteajoutpanier'),
                    $eb->lt('dateValidation', Parametrage::getDateDebutPanierApresValidationLimite())
                )
            )
        );

        return $criteria;
    }

    public function aNettoyer()
    {
        return $this->matching(self::criteriaANettoyer());
    }

    public function findUtilisateurPourDesinscriptionCreneau($creneau, $user)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.creneau = :creneau')
            ->setParameter('creneau', $creneau)
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUtilisateurPourDesinscriptionFormat($format, $user)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.formatActivite = :format')
            ->setParameter('format', $format)
            ->andWhere('i.utilisateur = :user')
            ->setParameter('user', $user)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInscriptionCreneauxBascule($listeActivite)
    {
        $qb = $this->createQueryBuilder('i')
            ->innerJoin('UcaBundle\Entity\FormatActivite', 'formAct', 'WITH', 'i.formatActivite = formAct.id')
            ->where('i.creneau IS NOT NULL')
            ->andWhere('i.statut NOT IN (:listeStatut)')
            ->andWhere('formAct.activite IN (:listeActivite)')
            ->setParameter('listeStatut', ['desinscrit', 'annule', 'ancienneinscription'])
            ->setParameter('listeActivite', $listeActivite)
    ;

        return $qb->getQuery()->getResult();
    }

    public function inscriptionParCreneauStatut($creneau, $statut)
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.creneau = :creneau')
            ->andWhere('i.statut LIKE :statut1')
            ->setParameter('statut1', '%'.$statut.'%')
            ->setParameter('creneau', $creneau)
        ;

        return count($qb->getQuery()->getResult()) > 0;
    }
}
