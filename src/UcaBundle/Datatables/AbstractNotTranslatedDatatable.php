<?php

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
