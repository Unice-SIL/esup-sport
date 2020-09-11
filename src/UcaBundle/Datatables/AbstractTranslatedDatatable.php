<?php

/*
 * Classe : AbstractTranslatedDatatable:
 *
 * Classe mère des datatables toute les classes de datatable sont liees aux entites
 * les tableau (liste) affichent les données et boutons selectionner dans les classes filels
 * Dans certains cas des requêtes personnalitées ajoutent des flitres supplémentaires.
 */

namespace UcaBundle\Datatables;

abstract class AbstractTranslatedDatatable extends AbstractUcaDatatable
{
    public function customizeQuery(&$query)
    {
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
    }
}
