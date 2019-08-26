<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigDataColumn;

class InscriptionAValiderDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('utilisateur.username', Column::class, array(
                'title' => $this->translator->trans('common.username'),
            ))
            ->add('utilisateur.nom', Column::class, array(
                'title' => $this->translator->trans('common.nom'),
            ))
            ->add('utilisateur.prenom', Column::class, array(
                'title' => $this->translator->trans('common.prenom'),
            ))
            ->add('date', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    (new VoirButton($this, 'UcaWeb_InscriptionAValiderVoir', ['id' => 'id']))->getConfig(),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Inscription';
    }

    public function getName()
    {
        return 'Inscription_datatable';
    }
}
