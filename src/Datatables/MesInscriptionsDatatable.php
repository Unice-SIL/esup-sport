<?php

/*
 * Classe - MesInscriptionesDatatable
 *
 * COntient les colonnes à afficher pour la page mes inscriptions
*/

namespace App\Datatables;

use App\Datatables\Button\InscriptionAjouterPanierButton;
use App\Datatables\Button\InscriptionAnnulerButton;
use App\Datatables\Button\InscriptionDesinscrireButton;
use App\Datatables\Column\TwigDataColumn;
use App\Datatables\Column\TwigVirtualColumn;
use App\Entity\Uca\Inscription;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;

class MesInscriptionsDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->addInvisibleColumns([
            'id',
            'statut',
            'utilisateur.id',
            'creneau.formatActivite.libelle',
            'creneau.serie.evenements.dateDebut',
            'creneau.serie.evenements.dateFin',
            'formatActivite.activite.libelle',
            'formatActivite.libelle',
            'reservabilite.evenement.dateDebut',
            'reservabilite.serie.dateDebut',
            'reservabilite.evenement.dateFin',
            'reservabilite.serie.dateFin',
            'reservabilite.ressource.libelle',
        ]);

        $qb = $this->em->createQueryBuilder();
        $qb1 = $this->em->createQueryBuilder();

        $this->columnBuilder
            ->add('Activite', TwigVirtualColumn::class, [
                'title' => 'Mes activités',
                'twigTemplate' => 'InscriptionData',
                'class_name' => 'hide-column-sm',
            ])
            ->add('date', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
                'searchable' => true,
            ])
            ->add('statutTraduit', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('common.statut'),
                'visible' => true,
                'twigTemplate' => 'Trans',
                'field' => 'statut',
            ])
            ->add('creneauActivite', TwigDataColumn::class, [
                'dql' => '('.$qb->select('a.libelle')
                    ->from(Inscription::class, 'i')
                    ->leftjoin('i.creneau', 'c')
                    ->leftjoin('c.formatActivite', 'f')
                    ->leftjoin('f.activite', 'a')
                    ->andWhere('i.id = inscription.id')
                    ->getDQL().')',

                'type_of_field' => 'string',
                'visible' => false,
                'searchable' => false,
            ])
            ->add('reservabiliteActivite', TwigDataColumn::class, [
                'dql' => '('.$qb1->select('a1.libelle')
                    ->from(Inscription::class, 'i1')
                    ->leftjoin('i1.formatActivite', 'f1')
                    ->leftjoin('f1.activite', 'a1')
                    ->andWhere('i1.id = inscription.id')
                    ->getDQL().')',
                'type_of_field' => 'string',
                'visible' => false,
                'searchable' => false,
            ])

            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new InscriptionAnnulerButton($this, 'UcaWeb_MesInscriptionsAnnuler', ['id' => 'id']))->getConfig(),
                    (new InscriptionAjouterPanierButton($this, 'UcaWeb_MesInscriptionsAjoutPanier', ['id' => 'id']))->getConfig(),
                    (new InscriptionDesinscrireButton($this, 'UcaWeb_MesInscriptionsSeDesinscrire', ['id' => 'id']))->getConfig(),
                ],
            ])
        ;
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
