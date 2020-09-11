<?php

/*
 * Classe : AbstractNOtTranslatedDatatable:
 *
 * Classe mère des datatables toute les classes de datatable sont liees aux entites
 * les tableau (liste) affichent les données et boutons selectionner dans les classes filels
 * Dans certains cas des requêtes personnalitées ajoutent des flitres supplémentaires.
*/

namespace UcaBundle\Datatables;

use Doctrine\ORM\Tools\Pagination\CountWalker;

abstract class AbstractNotTranslatedDatatable extends AbstractUcaDatatable
{
    public $distinct_count = false;

    public function customizeQuery(&$query)
    {
        $query->setHint(CountWalker::HINT_DISTINCT, false);
    }
}
