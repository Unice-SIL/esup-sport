<?php

/*
 * Classe - InscriptionAValiderDatatable
 *
 * COntient les champs à afficher pour la table des inscriptions en attente de validation
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\VoirButton;
use App\Datatables\Column\TwigDataColumn;

class InscriptionAValiderDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('utilisateur.username', Column::class, [
                'title' => $this->translator->trans('common.username'),
            ])
            ->add('utilisateur.nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
            ])
            ->add('utilisateur.prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
            ])
            ->add('date', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaWeb_InscriptionAValiderVoir', ['id' => 'id']))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\Inscription';
    }

    public function getName()
    {
        return 'Inscription_datatable';
    }
}
