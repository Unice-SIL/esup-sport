<?php

/*
 * Classe - FormatActiviteDatatable
 *
 * Liste les colonnes des format d'activité disponibles au sein d'une activité
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Button\VoirButton;
use App\Datatables\Column\TwigDataColumn;

class FormatActiviteDatatable extends AbstractTranslatedDatatable
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
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'class_name' => 'hide-column-md',
                'orderable' => false,
            ])
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('formatactivite.libelle'),
                'visible' => true,
            ])
            ->add('description', Column::class, [
                'title' => $this->translator->trans('common.description'),
                'visible' => true,
                'searchable' => true,
                'class_name' => 'hide-column',
            ])
            ->add('niveauxSportifs.libelle', Column::class, [
                'title' => 'niveauxSportifs',
                'data' => 'niveauxSportifs[,].libelle',
                'visible' => false,
            ])
            ->add('activite.libelle', Column::class, [
                'title' => 'Activité',
                'visible' => false,
            ])
            ->add('activite.classeActivite.libelle', Column::class, [
                'title' => 'classe Activité',
                'visible' => false,
            ])
            ->add('activite.classeActivite.typeActivite.libelle', Column::class, [
                'title' => 'Type Activité',
                'visible' => false,
            ])
            ->add('dateDebutEffective', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date.debut'),
                'twigTemplate' => 'Date',
            ])
            ->add('dateFinEffective', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date.fin'),
                'twigTemplate' => 'Date',
            ])
            /*->add('profilsUtilisateurs.libelle', Column::class, array(
                'title' => 'Profil Utilisateur',
                'data' => 'profilsUtilisateurs[,].libelle',
                'visible' => false,
            ))*/
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_FormatActiviteVoir', ['idActivite' => 'activite.id', 'id' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_LECTURE'))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_FormatActiviteModifier', ['idActivite' => 'activite.id', 'id' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_FormatActiviteSupprimer', ['idActivite' => 'activite.id', 'id' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'FormatActivite', 'objectId' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\FormatActivite';
    }

    public function getName()
    {
        return 'format_activite_datatable';
    }
}
