<?php

/*
 * Classe - NiveauSportifDatatable
 *
 * COntient les champs à afficher pour la table des NiveauSportifDatatable
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Style;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\SupprimerButton;
use App\Entity\Uca\NiveauSportif;

class NiveauSportifDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('common.libelle'),
                'searchable' => true,
                'orderable' => true,
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_NiveauSportifModifier', ['id' => 'id'], 'ROLE_GESTION_NIVEAUSPORTIF_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_NiveauSportifSupprimer', ['id' => 'id'], 'ROLE_GESTION_NIVEAUSPORTIF_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'NiveauSportif', 'objectId' => 'id'], 'ROLE_GESTION_NIVEAUSPORTIF_ECRITURE'))->getConfig(),
                ],
            ])
        ;

        $this->options->set([
            'classes' => '',
            'row_id' => 'id',
            'classes' => Style::BOOTSTRAP_4_STYLE,
            'search_in_non_visible_columns' => true,
        ]);
    }

    public function getEntity()
    {
        return NiveauSportif::class;
    }

    public function getName()
    {
        return 'NiveauSportif_datatable';
    }
}
