<?php

/*
 * Classe - LogoPartenaireRepository
 *
 * Requêtes à la base de données pour l'entité Logo Partenaire
*/

namespace UcaBundle\Repository;

class LogoPartenaireRepository extends \Doctrine\ORM\EntityRepository
{
    public function max($field)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('MAX(a.'.$field.')');
        $res = $qb->getQuery()->getSingleScalarResult();

        return empty($res) ? 0 : $res;
    }
}
