<?php

/*
 * Classe - EmailingDatatable:
 *
 * Donne les colonnes disponible pour la liste des utilisateurs
 * Permet de flitrer les utlisateurs poru l'emailing
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Column\TwigDataColumn;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use UcaBundle\Datatables\Filter\ActivitiesFilter;
use UcaBundle\Entity\Inscription;

class EmailingDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault(['options' => [
            'individual_filtering' => true,
            'order_cells_top' => true,
            'global_search_type' => 'like',
            'search_in_non_visible_columns' => false,
        ],
            'features' => ['state_save' => false],
        ]);
        $this->addInvisibleColumns([
            'id',
            'utilisateur.id',
            'creneau.formatActivite.libelle',
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
                // Filter> vérifier e paginator
                'filter' => [ActivitiesFilter::class, []],
            ])
            ->add('date', TwigDataColumn::class, [
                'title' => $this->translator->trans('common.date'),
                'twigTemplate' => 'Date',
            ])
            ->add('creneauActivite', TwigDataColumn::class, [
                'dql' => '('.$qb->select('a.libelle')
                    ->from('UcaBundle:inscription', 'i')
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
                    ->from('UcaBundle:inscription', 'i1')
                    ->leftjoin('i1.formatActivite', 'f1')
                    ->leftjoin('f1.activite', 'a1')
                    ->andWhere('i1.id = inscription.id')
                    ->getDQL().')',
                'type_of_field' => 'string',
                'visible' => false,
                'searchable' => false,
            ])
            ->add('formatActivite.listeEncadrants', Column::class, [
                'visible' => false,
            ])
            ->add('formatActivite.listeLieux', Column::class, [
                'visible' => false,
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
