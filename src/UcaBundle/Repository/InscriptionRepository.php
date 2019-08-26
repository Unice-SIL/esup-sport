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
}
