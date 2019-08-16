<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class ProfilUtilisateurDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('libelle', Column::class, array(
                'title' => $this->translator->trans('common.libelle'),
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'ProfilUtilisateurModifier', ['id' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'ProfilUtilisateurSupprimer', ['id' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\ProfilUtilisateur';
    }

    public function getName()
    {
        return 'ProfilUtilisateur_datatable';
    }
}
