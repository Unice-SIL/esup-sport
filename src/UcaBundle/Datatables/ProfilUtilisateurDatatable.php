<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;

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
            ->add('nbMaxInscriptions', Column::class, array(
                'title' => $this->translator->trans('profilutilisateur.nbmaxinscriptions.libelle'),
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    (new ModifierButton($this, 'UcaGest_ProfilUtilisateurModifier', ['id' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ProfilUtilisateurSupprimer', ['id' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ProfilUtilisateur', 'objectId' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'))->getConfig(),
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
