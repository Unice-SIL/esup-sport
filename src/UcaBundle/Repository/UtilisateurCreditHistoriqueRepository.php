<?php

/*
 * Classe - UtilisateurCreditHistoriqueRepository
 *
 * Requêtes à la base de données pour l'entité utilisateur crédits historiques
*/

namespace UcaBundle\Repository;

class UtilisateurCreditHistoriqueRepository extends \Doctrine\ORM\EntityRepository
{
    public function findExtractedCredits($dateDebut, $dateFin)
    {
        $qb = $this->createQueryBuilder('credit');
        $er = $qb->expr();
        if (null !== $dateDebut && null !== $dateFin) {
            $qb->andWhere($er->between('credit.date', $dateDebut, $dateFin));
        } elseif (null !== $dateDebut && null == $dateFin) {
            $qb->andWhere($er->gt('credit.date', $dateDebut));
        } elseif (null == $dateDebut && null !== $dateFin) {
            $qb->andWhere($er->lt('credit.date', $dateFin));
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllCreditsByOperation($date, $recherche, $operation)
    {
        $qb = $this->createQueryBuilder('credit');
        $er = $qb->expr();
        $qb
            ->andWhere($er->eq('credit.statut', ':statut'))
            ->setParameter('statut', 'valide')
        ;
        if (is_array($operation)) {
            $qb
                ->andWhere('credit.operation IN (:operation)')
                ->setParameter('operation', $operation)
                ;
        } else {
            $qb
                ->andWhere($er->eq('credit.operation', ':operation'))
                ->setParameter('operation', $operation)
            ;
        }
        if (null != $date) {
            $qb
                ->andWhere($er->between('credit.date', ':dtDebut', ':dtFin'))
                ->setParameters([
                    'dtDebut' => \DateTime::createFromFormat('Y-m-d', $date)->setTime(0, 0, 0),
                    'dtFin' => \DateTime::createFromFormat('Y-m-d', $date)->setTime(23, 59, 59),
                ])
            ;
        }

        if (null != $recherche) {
            $qb->andWhere(
                $er->orx(
                    $er->eq('credit.prenom', ':recherche'),
                    $er->eq('credit.nom', ':recherche')
                )
            );
            $qb->setParameter('recherche', $recherche);
        }

        return $qb->getQuery()->getResult();
    }
}
