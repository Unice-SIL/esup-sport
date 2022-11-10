<?php

/*
 * Classe - ProfilUtilisateurDatatable
 *
 * COntient les colonnes à afficher pour la liste des profils utilisateur
*/

namespace App\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use App\Datatables\Button\LogButton;
use App\Datatables\Button\ModifierButton;
use App\Datatables\Button\SupprimerButton;
use App\Datatables\Column\TwigVirtualColumn;

class ProfilUtilisateurDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'parent.libelle'
        ]);

        $this->columnBuilder
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('common.libelle'),
            ])
            ->add('parent', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('profilutilisateur.parent'),
                'twigTemplate' => 'ProfilParent',
                'orderable' => true,
                'order_column' => 'parent.libelle'
            ])
            ->add('nbMaxInscriptions', Column::class, [
                'title' => $this->translator->trans('profilutilisateur.nbmaxinscriptions.libelle'),
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new ModifierButton($this, 'UcaGest_ProfilUtilisateurModifier', ['id' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_ProfilUtilisateurSupprimer', ['id' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'ProfilUtilisateur', 'objectId' => 'id'], 'ROLE_GESTION_PROFIL_UTILISATEUR_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'App\Entity\Uca\ProfilUtilisateur';
    }

    public function getName()
    {
        return 'ProfilUtilisateur_datatable';
    }
}
