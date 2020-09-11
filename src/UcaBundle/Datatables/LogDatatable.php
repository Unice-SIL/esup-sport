<?php

/*
 * Classe - LogDatatable
 *
 * COntient les champs à afficher pour la table des logs
*/

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
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('data', Column::class, [
                'title' => 'Data',
                'visible' => false,
            ])
            ->add('loggedAt', DateTimeColumn::class, [
                'title' => $this->translator->trans('common.date'),
            ])
            ->add('username', Column::class, [
                'title' => $this->translator->trans('common.utilisateur'),
            ])
            ->add('data', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.modifications'),
                'twigTemplate' => 'LogData',
            ])
            ->add('action', Column::class, [
                'title' => $this->translator->trans('common.evenement'),
            ])
        ;
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
