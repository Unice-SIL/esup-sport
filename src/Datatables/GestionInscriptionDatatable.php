<?php

/*
 * Classe - GestionInscriptionDatatable::
 *
 * Liste des colonnes afficher dans le reporting Inscription
 * Permet de définir les flitres.
*/

namespace App\Datatables;

use App\Datatables\Button\InscriptionAjouterPanierButton;
use App\Datatables\Button\InscriptionAnnulerButton;
use App\Datatables\Button\InscriptionDesinscrireButton;
use App\Datatables\Button\InscriptionValiderButton;
use App\Datatables\Column\TwigDataColumn;
use App\Datatables\Column\TwigVirtualColumn;
use App\Datatables\Filter\ActivitiesFilter;
use App\Datatables\Filter\SelectInVirtualColumnFilter;
use App\Entity\Uca\Inscription;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class GestionInscriptionDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault(['options' => [
            'individual_filtering' => true,
            'order_cells_top' => true,
            'global_search_type' => 'like',
            'dom' => 'lrt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        ],
            'features' => ['state_save' => false],
        ]);
        $this->addInvisibleColumns([
            'id',
            'statut',
            'utilisateur.id',
            'creneau.formatActivite.libelle',
            'creneau.listeEncadrants',
            'creneau.serie.evenements.dateDebut',
            'creneau.serie.evenements.dateFin',
            'formatActivite.activite.libelle',
            'formatActivite.libelle',
            'formatActivite.lieu.etablissement.libelle',
            'formatActivite',
            'reservabilite.evenement.dateDebut',
            'reservabilite.evenement.dateFin',
            'reservabilite.ressource.libelle',
        ]);

        $qb = $this->em->createQueryBuilder();
        $qb1 = $this->em->createQueryBuilder();
        $qb2 = $this->em->createQueryBuilder();
        $qb3 = $this->em->createQueryBuilder();

        $this->columnBuilder
            ->add('utilisateur.nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
                'searchable' => true,
            ])
            ->add('utilisateur.prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
                'searchable' => true,
            ])
            ->add('Activite', TwigVirtualColumn::class, [
                'title' => $this->translator->trans('activite.list.title'),
                'twigTemplate' => 'InscriptionData',
                'class_name' => 'hide-column-sm',
                'searchable' => true,
                'search_column' => 'formatActivite',
                'filter' => [ActivitiesFilter::class, []],
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
                'searchable' => true,
                'search_column' => 'statut',
                'filter' => [SelectInVirtualColumnFilter::class, [
                    'classes' => '',
                    'initial_search' => '',
                    'select_options' => [
                        '' => $this->translator->trans('traduction.tous'),
                        'annule' => $this->translator->trans('common.annule'),
                        'valide' => $this->translator->trans('common.valide'),
                        'attentepaiement' => $this->translator->trans('common.attentepaiement'),
                        'attentevalidationencadrant' => $this->translator->trans('common.attentevalidationencadrant'),
                        'attentevalidationgestionnaire' => $this->translator->trans('common.attentevalidationgestionnaire'),
                        'ancienneinscription' => $this->translator->trans('common.ancienneinscription'),
                        'desinscriptionadministrative' => $this->translator->trans('common.desinscriptionadministrative'),
                    ],
                ]],
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
            ->add('commandeTermine', Column::class, [
                'dql' => '('.
                    $qb2->select($qb2->expr()->countDistinct('com.statut'))
                    ->from(Inscription::class, 'i2')
                    ->leftJoin('i2.commandeDetails', 'comd')
                    ->leftJoin('comd.commande', 'com')
                    ->andWhere('i2.id = inscription.id')
                    ->andWhere('com.statut = \'termine\'')
                    ->getDQL()
                .')',
                'visible' => false,
            ])
            ->add('typePaiement', Column::class, [
                'dql' => '('.
                    $qb3->select($qb3->expr()->countDistinct('com1.typePaiement'))
                    ->from(Inscription::class, 'i3')
                    ->leftJoin('i3.commandeDetails', 'comd1')
                    ->leftJoin('comd1.commande', 'com1')
                    ->andWhere('i3.id = inscription.id')
                    ->andWhere('com1.typePaiement = \'PAYBOX\'')
                    ->getDQL()
                .')',
                'visible' => false,
            ])
            ->add('formatActivite.listeEncadrants', Column::class, [
                'visible' => false,
            ])
            ->add('formatActivite.listeLieux', Column::class, [
                'visible' => false,
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new InscriptionAnnulerButton($this, 'UcaWeb_MesInscriptionsAnnuler', ['id' => 'id']))->getConfig(),
                    (new InscriptionAjouterPanierButton($this, 'UcaWeb_MesInscriptionsAjoutPanier', ['id' => 'id']))->getConfig(),
                    (new InscriptionDesinscrireButton($this, 'UcaWeb_MesInscriptionsSeDesinscrire', ['id' => 'id']))->getConfig(),
                    (new InscriptionValiderButton($this, 'UcaGest_ValiderInscription', ['id' => 'id']))->getConfig(),
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
        return 'gestioninscription_datatable';
    }
}
