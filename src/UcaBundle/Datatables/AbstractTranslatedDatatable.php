<?php

namespace UcaBundle\Datatables;

abstract class AbstractTranslatedDatatable extends AbstractUcaDatatable
{
    public function customizeQuery(&$query) {
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
    }
}
