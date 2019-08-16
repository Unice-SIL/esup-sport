<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Column\TwigDataColumn;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;

class FormatActiviteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('image', ImageColumn::class, array(
                'title' => 'Image',
                'imagine_filter' => 'thumb_small',
                'relative_path' => 'upload/public/image',
                'class_name' => 'hide-column-md'
            ))
            ->add('libelle', Column::class, array(
                'title' => $this->translator->trans('formatactivite.libelle'),
                'visible' => true,
            ))
            ->add('description', Column::class, array(
                'title' => $this->translator->trans('common.description'),
                'visible' => true,
                'searchable' => true,
                'class_name' => 'hide-column'
            ))
            ->add('niveauxSportifs.libelle', Column::class, array(
                'title' => 'niveauxSportifs',
                'data' => 'niveauxSportifs[,].libelle',
                'visible' => false,
            ))
            ->add('activite.libelle', Column::class, array(
                'title' => 'Activité',
                'visible' => false,
            ))
            ->add('activite.classeActivite.libelle', Column::class, array(
                'title' => 'classe Activité',
                'visible' => false,
            ))
            ->add('activite.classeActivite.typeActivite.libelle', Column::class, array(
                'title' => 'Type Activité',
                'visible' => false,
            ))
            ->add('dateDebutEffective', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.date.debut'),
                'twigTemplate' => 'Date',
            ))
            ->add('dateFinEffective', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.date.fin'),
                'twigTemplate' => 'Date',
            ))
            ->add('profilsUtilisateurs.libelle', Column::class, array(
                'title' => 'Profil Utilisateur',
                'data' => 'profilsUtilisateurs[,].libelle',
                'visible' => false,
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Voir', 'FormatActiviteVoir', ['idActivite' => 'activite.id', 'id' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_LECTURE'),
                    $this->getActionBoutonConfig('Modifier', 'FormatActiviteModifier', ['idActivite' => 'activite.id', 'id' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'FormatActiviteSupprimer',  ['idActivite' => 'activite.id', 'id' => 'id'], 'ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'FormatActivite', 'objectId' => 'id']),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\FormatActivite';
    }

    public function getName()
    {
        return 'format_activite_datatable';
    }
}
