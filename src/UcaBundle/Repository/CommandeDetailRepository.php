<?php

namespace UcaBundle\Repository;

use Doctrine\Common\Collections\Criteria;

class CommandeDetailRepository extends \Doctrine\ORM\EntityRepository
{

    public function criteriaByAutorisation($autorisation)
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('typeAutorisation', $autorisation));
    }
}
