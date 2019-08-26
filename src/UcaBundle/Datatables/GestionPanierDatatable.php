<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigDataColumn;

class GestionPanierDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('utilisateur.username', Column::class, array(
                'title' => $this->translator->trans('common.utilisateur'),
                'class_name' => 'hide-column-sm'
            ))
            ->add('utilisateur.nom', Column::class, array(
                'title' => $this->translator->trans('common.nom'),
            ))
            ->add('utilisateur.prenom', Column::class, array(
                'title' => $this->translator->trans('common.prenom'),
            ))
            ->add('utilisateur.email', Column::class, array(
                'title' => $this->translator->trans('common.email'),
                'class_name' => 'hide-column-md'
            ))
            ->add('statut', TwigDataColumn::class, array(
                'title' => $this->translator->trans('common.statut'),
                'twigTemplate' => 'Trans',
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaWeb_CommandeEnAttenteVoir', ['id' => 'id'], 'ROLE_GESTION_PAIEMENT_COMMANDE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaWeb_CommandeEnAttenteSupprimer', ['id' => 'id'], 'ROLE_GESTION_PAIEMENT_COMMANDE'))->getConfig(),
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Commande';
    }

    public function getName()
    {
        return 'Commande_datatable';
    }
}
