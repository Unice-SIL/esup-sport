<?php

/*
 * Classe - RessourceDatatable
 *
 * COntient les colonnes à afficher pour la liste des ressources
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Button\VoirButton;
use App\Datatables\Column\TwigVirtualColumn;

class RessourceDatatable extends AbstractTranslatedDatatable
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
            ])
            ->add('description', Column::class, [
                'title' => $this->translator->trans('common.description'),
                'class_name' => 'hide-column-md',
            ])
            ->add('FormatRessource', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.type'),
                'twigTemplate' => 'FormatRessource',
                'class_name' => 'hide-column-sm',
            ])
            ->add('etablissement.libelle', Column::class, [
                'title' => $this->translator->trans('ressource.etablissement'),
                'default_content' => '(none)',
                'searchable' => true,
                'orderable' => true,
            ])

            // ->add('superficie', Column::class, array(
            //     'title' => 'Superficie',
            // ))
            // ->add('nomenclatureRus', Column::class, array(
            //     'title' => 'Code RUS',
            // ))
            // ->add('capaciteSportifs', Column::class, array(
            //     'title' => 'Capacite Sportifs',
            // ))
            // ->add('capaciteSpectateurs', Column::class, array(
            //     'title' => 'Capacite Spectateurs',
            // ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_RessourceVoir', ['id' => 'id']))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_RessourceModifier', ['id' => 'id'], 'ROLE_GESTION_RESSOURCE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_RessourceSupprimer', ['id' => 'id'], 'ROLE_GESTION_RESSOURCE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Ressource', 'objectId' => 'id'], 'ROLE_GESTION_RESSOURCE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\Ressource';
    }

    public function getName()
    {
        return 'Ressource_datatable';
    }
}
