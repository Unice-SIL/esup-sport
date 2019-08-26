<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use UcaBundle\Datatables\Button\InscriptionAjouterPanierButton;
use UcaBundle\Datatables\Button\InscriptionAnnulerButton;
use UcaBundle\Datatables\Column\TwigDataColumn;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use UcaBundle\Entity\Inscription;

class MesInscriptionsDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'statut',
            'utilisateur.id',
            // 'creneau.formatActivite.activite.libelle',
            'creneau.formatActivite.libelle',
            'creneau.serie.evenements.dateDebut',
            'creneau.serie.evenements.dateFin',
            'formatActivite.activite.libelle',
            'formatActivite.libelle',
            'reservabilite.evenement.dateDebut',
            'reservabilite.evenement.dateFin',
            'reservabilite.ressource.libelle',
        ]);

        $this->columnBuilder
            ->add('Activite', TwigVirtualColumn::class, [
                'title' => "Mes activités",
                'twigTemplate' => 'InscriptionData',
                'class_name' => 'hide-column-sm',
            ])
            ->add('date', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
            ])
            ->add('statutTraduit', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.statut'),
                'visible' => true,
                'twigTemplate' => 'Trans',
                'field' => 'statut',
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new InscriptionAnnulerButton($this, 'UcaWeb_MesInscriptionsAnnuler', ['id' => 'id']))->getConfig(),
                    (new InscriptionAjouterPanierButton($this, 'UcaWeb_MesInscriptionsAjoutPanier', ['id' => 'id']))->getConfig()
                ]
            ]);
    }

    public function getEntity()
    {
        return Inscription::class;
    }

    public function getName()
    {
        return 'inscription_datatable';
    }
}
