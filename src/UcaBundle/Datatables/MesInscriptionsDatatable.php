<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use UcaBundle\Datatables\Button\InscriptionAjouterPanierButton;
use UcaBundle\Datatables\Button\InscriptionAnnulerButton;
use UcaBundle\Datatables\Button\VoirButton;
use UcaBundle\Datatables\Column\TwigDataColumn;
use UcaBundle\Datatables\Column\TwigVirtualColumn;
use UcaBundle\Entity\Inscription;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\InscriptionDesinscrireButton;

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
            'reservabilite.evenement.dateFin',
            'reservabilite.ressource.libelle',
        ]);
        
         $qb = $this->em->createQueryBuilder();
         $qb1 = $this->em->createQueryBuilder();

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
            ->add('creneauActivite', TwigDataColumn::class, [
                'dql' => 
                "(".$qb->select('a.libelle')
                ->from('UcaBundle:inscription', 'i')
                ->leftjoin('i.creneau', 'c')
                ->leftjoin('c.formatActivite', 'f')
                ->leftjoin('f.activite', 'a')
                ->andWhere("i.id = inscription.id")
                ->getDQL().")"
                ,

                'type_of_field' => 'string',
                'visible' => false,
                'searchable' => false
            ])
            ->add('reservabiliteActivite', TwigDataColumn::class, [
                'dql' => 
                "(".$qb1->select('a1.libelle')
                ->from('UcaBundle:inscription', 'i1')
                ->leftjoin('i1.formatActivite', 'f1')
                ->leftjoin('f1.activite', 'a1')
                ->andWhere("i1.id = inscription.id")
                ->getDQL().")"   
                ,
                'type_of_field' => 'string',
                'visible' => false,
                'searchable' => false
            ])   


            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new InscriptionAnnulerButton($this, 'UcaWeb_MesInscriptionsAnnuler', ['id' => 'id']))->getConfig(),
                    (new InscriptionAjouterPanierButton($this, 'UcaWeb_MesInscriptionsAjoutPanier', ['id' => 'id']))->getConfig(),
                    (new InscriptionDesinscrireButton($this, 'UcaWeb_MesInscriptionsSeDesinscrire', ['id' => 'id']))->getConfig(),
                    // (new VoirButton($this, 'UcaWeb_MesInscriptionsVoir', ['id' => 'id']))->getConfig(),
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
