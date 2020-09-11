<?php

/*
 * Classe - EtablissementDatatable:
 *
 * Donne les colonnes affichés dans la liste des établissements.
*/

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use UcaBundle\Datatables\Button\LogButton;
use UcaBundle\Datatables\Button\ModifierButton;
use UcaBundle\Datatables\Button\SupprimerButton;
use UcaBundle\Datatables\Button\VoirButton;

class EtablissementDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, [
                'title' => 'Id',
                'visible' => false,
            ])
            ->add('libelle', Column::class, [
                'title' => $this->translator->trans('common.libelle'),
            ])
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    (new VoirButton($this, 'UcaGest_EtablissementVoir', ['id' => 'id'], 'ROLE_GESTION_ETABLISSEMENT_LECTURE'))->getConfig(),
                    (new ModifierButton($this, 'UcaGest_EtablissementModifier', ['id' => 'id'], 'ROLE_GESTION_ETABLISSEMENT_ECRITURE'))->getConfig(),
                    (new SupprimerButton($this, 'UcaGest_EtablissementSupprimer', ['id' => 'id'], 'ROLE_GESTION_ETABLISSEMENT_ECRITURE'))->getConfig(),
                    (new LogButton($this, 'UcaGest_LogLister', ['objectClass' => 'Etablissement', 'objectId' => 'id'], 'ROLE_GESTION_ETABLISSEMENT_ECRITURE'))->getConfig(),
                ],
            ])
        ;
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Etablissement';
    }

    public function getName()
    {
        return 'Etablissement_datatable';
    }
}
