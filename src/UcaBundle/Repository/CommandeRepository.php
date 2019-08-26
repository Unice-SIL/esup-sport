<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use UcaBundle\Service\Common\Parametrage;

class CommandeRepository extends \Doctrine\ORM\EntityRepository
{
    public static function criteriaByStatut($statutListe)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->in('statut', $statutListe));
    }

    public static function criteriaANettoyer()
    {
        $eb = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $eb->orX(
                $eb->andX(
                    $eb->eq('statut', 'apayer'),
                    $eb->eq('typePaiement', 'PAYBOX'),
                    $eb->eq('moyenPaiement', 'cb'),
                    $eb->lt('dateCommande', Parametrage::getDateDebutCbLimite())
                ),
                $eb->andX(
                    $eb->eq('statut', 'apayer'),
                    $eb->eq('typePaiement', 'BDS'),
                    $eb->lt('dateCommande', Parametrage::getDateDebutBdsLimite())
                ),
                $eb->andX(
                    $eb->eq('statut', 'panier'),
                    $eb->lt('datePanier', Parametrage::getDateDebutPanierLimite())
                )
            )
        );
        return $criteria;
    }

    public function max($field)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('MAX(c.' . $field . ')');
        $res = $qb->getQuery()->getSingleScalarResult();
        return empty($res) ? 0 : $res;
    }

    public function aNettoyer()
    {
        return $this->matching(self::criteriaANettoyer());
    }
}
