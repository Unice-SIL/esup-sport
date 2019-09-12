<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;

class EntityRepository extends \Doctrine\ORM\EntityRepository
{
    public static function criteriaBy($crits)
    {
        $criteria = Criteria::create();
        foreach ($crits as $crit) {
            $critProperty = $crit[0];
            $critOperator = $crit[1];
            $critValue = $crit[2];
            $where = call_user_func_array([Criteria::expr(), $critOperator], [$critProperty, $critValue]);
            $criteria->andWhere($where);
        }
        return $criteria;
    }
}
