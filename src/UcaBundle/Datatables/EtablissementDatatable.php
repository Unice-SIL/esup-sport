<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class EtablissementDatatable extends AbstractTranslatedDatatable
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
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => [
                    $this->getActionBoutonConfig('Voir','EtablissementVoir', ['id' => 'id']),
                    $this->getActionBoutonConfig('Modifier', 'EtablissementModifier', ['id' => 'id'], 'ROLE_GESTION_ETABLISSEMENT_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'EtablissementSupprimer', ['id' => 'id'], 'ROLE_GESTION_ETABLISSEMENT_ECRITURE')
                ]
            ]);
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
