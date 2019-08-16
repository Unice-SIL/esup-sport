<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class UtilisateurDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('username', Column::class, array(
                'title' => $this->translator->trans('common.utilisateur'),
                'class_name' => 'hide-column-sm'
            ))
            ->add('nom', Column::class, array(
                'title' => $this->translator->trans('common.nom'),
            ))
            ->add('prenom', Column::class, array(
                'title' => $this->translator->trans('common.prenom'),
            ))
            ->add('email', Column::class, array(
                'title' => $this->translator->trans('common.email'),
                'class_name' => 'hide-column-md'
            ))
            ->add('groups.name', Column::class, array(
                'title' => $this->translator->trans('common.groups'),
                'data' => 'groups[,].name',
                'class_name' => 'hide-column-xs'
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    $this->getActionBoutonConfig('Voir', 'UtilisateurVoir', ['id' => 'id'], 'ROLE_GESTION_UTILISATEUR_LECTURE'),
                    $this->getActionBoutonConfig('Modifier', 'UtilisateurModifier', ['id' => 'id'], 'ROLE_GESTION_UTILISATEUR_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'UtilisateurSupprimer', ['id' => 'id'], 'ROLE_GESTION_UTILISATEUR_ECRITURE')
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Utilisateur';
    }

    public function getName()
    {
        return 'Utilisateur_datatable';
    }
}
