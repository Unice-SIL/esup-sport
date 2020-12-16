<?php

/*
 * Classe - UtilisateurCreditHistoriqueRepository
 *
 * Requêtes à la base de données pour l'entité utilisateur crédits historiques
*/

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;

class UtilisateurCreditHistoriqueRepository extends \Doctrine\ORM\EntityRepository
{
    public static function crieriaByStatut($statut, $isExcel)
    {
        if ($isExcel) {
            $expr = Criteria::expr()->contains('credit.statut', '%'.$statut.'%');
        } else {
            $expr = Criteria::expr()->eq('credit.statut', $statut);
        }

        return Criteria::create()->andWhere($expr);
    }

    public static function criteriaByMontant($montant)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('credit.montant', $montant))
        ;
    }

    public static function criteriaByOperation($operation, $isExcel)
    {
        if (is_array($operation)) {
            $expr = Criteria::expr()->in('credit.operation', $operation);
        } elseif ($isExcel) {
            $expr = Criteria::expr()->contains('credit.operation', $operation);
        } else {
            $expr = Criteria::expr()->eq('credit.operation', $operation);
        }

        return Criteria::create()->andWhere($expr);
    }

    public static function criteriaBetweenDates($startDate, $endDate)
    {
        $criteria = Criteria::create();
        $er = $criteria->expr();

        if (null !== $startDate) {
            $criteria->andWhere($er->gt('credit.date', $startDate));
        }
        if (null !== $endDate) {
            $criteria->andWhere($er->lt('credit.date', $endDate));
        }

        return $criteria;
    }

    public static function criteriaByUtilisateur($nom, $prenom)
    {
        $criteria = Criteria::create();
        $er = $criteria->expr();

        if (null !== $prenom) {
            $criteria->andWhere($er->contains('utilisateur.prenom', '%'.$prenom.'%'));
        }
        if (null !== $nom) {
            $criteria->andWhere($er->contains('utilisateur.nom', '%'.$nom.'%'));
        }

        return $criteria;
    }

    public static function criteriaByRecherche($recherche, $isExcel)
    {
        $criteria = Criteria::create();
        $er = $criteria->expr();
        $criteria->andWhere($er->orx(
            $er->contains('utilisateur.prenom', '%'.$recherche.'%'),
            $er->contains('utilisateur.nom', '%'.$recherche.'%'),
            $er->contains('credit.statut', '%'.$recherche.'%'),
            $er->contains('credit.montant', '%'.$recherche.'%')
        ));
        if ($isExcel) {
            $criteria->orWhere($er->contains('credit.operation', '%'.$recherche.'%'));
        }

        return $criteria;
    }

    public function findExtractedCredits($dateDebut, $dateFin, $nom, $prenom, $recherche, $operation, $statut, $montant, $isExcel)
    {
        $qb = $this->createQueryBuilder('credit');
        $qb->join('credit.utilisateur', 'utilisateur');

        if (null !== $dateDebut || null !== $dateFin) {
            $qb->addCriteria(self::criteriaBetweenDates($dateDebut, $dateFin));
        }
        if (null !== $recherche) {
            $qb->addCriteria(self::criteriaByRecherche($recherche, $isExcel));
        }
        if (null !== $montant) {
            $qb->addCriteria(self::criteriaByMontant((float) $montant));
        }
        if (null !== $nom || null !== $prenom) {
            $qb->addCriteria(self::criteriaByUtilisateur($nom, $prenom));
        }
        if (null !== $operation) {
            $qb->addCriteria(self::criteriaByOperation($operation, $isExcel));
        }
        if (null !== $statut) {
            $qb->addCriteria(self::crieriaByStatut($statut, $isExcel));
        }
        /*dump($qb->getQuery());
        dump($qb->getParameters());
        dump($qb->getQuery()->getResult());
        die;*/

        return $qb->getQuery()->getResult();
    }
}
