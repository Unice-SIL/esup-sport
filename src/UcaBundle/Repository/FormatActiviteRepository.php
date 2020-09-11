<?php

/*
 * Classe - FormatActiviteRepository
 *
 * Requêtes à la base de données pour l'entité format d'activité
 * Il s'agit de methodes globales aux trois formats
*/

namespace UcaBundle\Repository;

use UcaBundle\Service\Common\Previsualisation;

class FormatActiviteRepository extends \Doctrine\ORM\EntityRepository
{
    public function enCoursPublication($qb, $alias = 'f')
    {
        $qb->andWhere($alias.'.statut = 1')
            ->andWhere($alias.'.dateDebutPublication <= :today')
            ->andWhere($alias.'.dateFinPublication >= :today')
            ->setParameter('today', new \Datetime('now'))
        ;
    }

    public function findFormatPublie($activite = null, $user)
    {
        $qb = $this
            ->createQueryBuilder('f')
        ;

        if (null !== $user) {
            $qb
                ->leftJoin('f.profilsUtilisateurs', 'fp')
                ->leftJoin('fp.profilUtilisateur', 'p')
                ->leftJoin('p.utilisateur', 'u')
                ->andWhere('u.id = :idUtilisateur')
                ->setParameter('idUtilisateur', $user->getId())
            ;
        }

        if (!Previsualisation::$IS_ACTIVE) {
            $this->enCoursPublication($qb);
        }

        if (!empty($activite)) {
            $qb->andWhere('f.activite = :activite')
                ->setParameter('activite', $activite)
            ;
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }
}
