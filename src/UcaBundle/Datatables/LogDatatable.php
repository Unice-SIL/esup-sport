<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\DateTimeColumn;
use UcaBundle\Datatables\Column\TwigDataColumn;

class LogDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('data', Column::class, array(
                'title' => 'Data',
                'visible' => false,
            ))
            ->add('loggedAt', DateTimeColumn::class, array(
                'title' => $this->translator->trans('common.date'),
            ))
            ->add('username', Column::class, array(
                'title' => $this->translator->trans('common.utilisateur'),
            ))
            ->add('data', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.modifications'),
                'twigTemplate' => 'LogData',
            ))
            ->add('action', Column::class, array(
                'title' => $this->translator->trans('common.evenement'),
            ));
    }

    public function getEntity()
    {
        return 'Gedmo\Loggable\Entity\LogEntry';
    }

    public function getName()
    {
        return 'Log_datatable';
    }
}
