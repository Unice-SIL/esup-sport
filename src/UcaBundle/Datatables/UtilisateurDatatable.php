<?php

/*
 * Classe - UtilisateurDatatable
 *
 * COntient les colonnes à afficher pour la liste des utilisateurs
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Button\UtilisateurBloquerButton;
use UcaBundle\Datatables\Button\UtilisateurDebloquerButton;
use UcaBundle\Datatables\Button\VoirButton;

class UtilisateurDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault([
            'options' => [
                'individual_filtering' => true,
                'individual_filtering_position' => 'head',
                'order_cells_top' => true,
            ],
        ]);

        $this->addInvisibleColumns([
            'id',
            'statut.id',
            // 'username',
            'enabled',
        ]);

        $this->columnBuilder
            ->add('nom', Column::class, [
                'title' => $this->translator->trans('common.nom'),
            ])
            ->add('prenom', Column::class, [
                'title' => $this->translator->trans('common.prenom'),
            ])
            ->add('email', Column::class, [
                'title' => $this->translator->trans('common.email'),
                'class_name' => 'hide-column-md',
            ])
            ->add('groups.libelle', Column::class, [
                'title' => $this->translator->trans('common.groups'),
                'data' => 'groups[, ].libelle',
                'class_name' => 'hide-column-xs',
                'orderable' => false,
            ])
            ->add('statut.libelle', Column::class, [
                'title' => $this->translator->trans('utilisateur.statut.datatable'),
                'class_name' => 'hide-column-xs',
                'default_content' => '',
                'filter' => [
                    SelectFilter::class, [
                        'search_type' => 'eq',
                        'select_options' => ['' => $this->translator->trans('common.all')]
                            + $this->getStatutUtilisateur(),
                    ],
                ],
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_UtilisateurVoir', ['id' => 'id'], 'ROLE_GESTION_UTILISATEUR_LECTURE'))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_UtilisateurModifier', ['id' => 'id'], 'ROLE_GESTION_UTILISATEUR_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_UtilisateurSupprimer', ['id' => 'id'], 'ROLE_GESTION_UTILISATEUR_ECRITURE'))->getConfig(),
                    (new UtilisateurBloquerButton($this, 'UcaGest_UtilisateurBloquer', ['id' => 'id']))->getConfig(),
                    (new UtilisateurDebloquerButton($this, 'UcaGest_UtilisateurBloquer', ['id' => 'id']))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Utilisateur';
    }

    public function getName()
    {
        return 'Utilisateur_datatable';
    }

    public function getStatutUtilisateur($id = 'id', $libelle = 'libelle')
    {
        $em = $this->getEntityManager();
        $statuts = $em->getRepository('UcaBundle:StatutUtilisateur')->findAll();
        $tab = $this->getOptionsArrayFromEntities($statuts, $id, $libelle);
        foreach ($tab as $key => $value) {
            $selectOptions[$value] = $value;
        }

        return $selectOptions;
    }
}
