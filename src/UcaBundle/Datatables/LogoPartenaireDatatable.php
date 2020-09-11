<?php

/*
 * Classe - LogoPartenaireDatatable
 *
 * COntient les champs à afficher pour la table des logos partenaires
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Style;
use UcaBundle\Datatables\Button\DescendreButton;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\MonterButton;
use UcaBundle\Datatables\Button\SupprimerButton;

class LogoPartenaireDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => '',
                'actions' => [
                    (new MonterButton($this, null, ['id' => 'id'], 'ROLE_GESTION_LOGOPARTENAIRE_ECRITURE'))->getConfig(),
                    (new DescendreButton($this, null, ['id' => 'id'], 'ROLE_GESTION_LOGOPARTENAIRE_ECRITURE'))->getConfig(),
                ],
            ])
            ->add('ordre', Column::class, [
                'title' => $this->translator->trans('common.ordre'),
                'orderable' => true,
            ])
            ->add('image', ImageColumn::class, [
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'orderable' => false,
            ])
            ->add('nom', Column::class, [
                'title' => $this->translator->trans('logopartenaire.nom'),
                'visible' => true,
            ])
            ->add('lien', Column::class, [
                'title' => $this->translator->trans('logopartenaire.lien'),
                'visible' => true,
            ])
            ->add('description', Column::class, [
                'title' => $this->translator->trans('common.description'),
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new SupprimerButton($this, 'UcaGest_LogoPartenaireSupprimer', ['id' => 'id'], 'ROLE_GESTION_LOGOPARTENAIRE_ECRITURE'))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_LogoPartenaireModifier', ['id' => 'id'], 'ROLE_GESTION_LOGOPARTENAIRE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ImageFond', 'objectId' => 'id'], 'ROLE_GESTION_LOGOPARTENAIRE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
        $this->options->set([
            'classes' => '',
            'row_id' => 'id',
            'order' => [[2, 'asc']],
            'classes' => Style::BOOTSTRAP_4_STYLE,
            'search_in_non_visible_columns' => true,
        ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\LogoPartenaire';
    }

    public function getName()
    {
        return 'LogoPartenaire_datatable';
    }
}
