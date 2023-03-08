<?php

/*
 * Classe - LogoPartenaireDatatable
 *
 * COntient les champs à afficher pour la table des logos partenaires
*/

namespace App\Datatables;

use App\Datatables\Column\TwigDataColumn;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Style;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\SupprimerButton;

class PeriodeFermetureDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('dateDeb', TwigDataColumn::class, [
                'title' => $this->translator->trans('periodefermeture.datedeb'),
                'twigTemplate' => 'DateOnly',
            ])
            ->add('dateFin', TwigDataColumn::class, [
                'title' => $this->translator->trans('periodefermeture.datefin'),
                'twigTemplate' => 'DateOnly',
            ])
            ->add('description', Column::class, [
                'title' => $this->translator->trans('periodefermeture.description'),
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_PeriodeFermetureModifier', ['id' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_PeriodeFermetureSupprimer', ['id' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'PeriodeFermeture', 'objectId' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
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
        return 'App\Entity\Uca\PeriodeFermeture';
    }

    public function getName()
    {
        return 'PeriodeFermeture_datatable';
    }
}
