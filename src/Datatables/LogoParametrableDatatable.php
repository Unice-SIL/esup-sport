<?php

/*
 * Classe - LogoPartenaireDatatable
 *
 * COntient les champs à afficher pour la table des logos partenaires
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Style;
use App\Datatables\Button\DescendreButton;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\MonterButton;
use App\Datatables\Button\SupprimerButton;

class LogoParametrableDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('image', ImageColumn::class, [
                'title' => 'Logo',
                'imagine_filter' => 'thumb_logo_dt',
                'relative_path' => 'upload/public/images/logos',
                'orderable' => false,
            ])
            ->add('emplacement', Column::class, [
                'title' => $this->translator->trans('logoparametrable.emplacement'),
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_LogoParametrableModifier', ['id' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'LogoParametrable', 'objectId' => 'id'], 'ROLE_GESTION_PARAMETRAGE'))->getConfig(),
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
        return 'App\Entity\Uca\LogoParametrable';
    }

    public function getName()
    {
        return 'LogoParametrable_datatable';
    }
}
