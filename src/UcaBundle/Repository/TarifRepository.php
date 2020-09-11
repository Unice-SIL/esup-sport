<?php

/*
 * Classe - TarifRepository
 *
 * Requêtes à la base de données pour l'entité tarif
*/

namespace UcaBundle\Repository;

use Doctrine\ORM\QueryBuilder;

class TarifRepository extends \Doctrine\ORM\EntityRepository
{
    public function listAll(QueryBuilder $qb)
    {
        $qb
            ->Join('tarif.montants', 'montant')
            ->leftJoin('montant.profil', 'profil')
            ->Select('tarif,montant,profil')
            ->orderBy('profil.libelle')
        ;

        return $qb;
    }
}
