<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class TypeActiviteDatatable extends AbstractTranslatedDatatable
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
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'TypeActiviteModifier', ['id' => 'id'], 'ROLE_GESTION_TYPE_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Supprimer', 'TypeActiviteSupprimer', ['id' => 'id'], 'ROLE_GESTION_TYPE_ACTIVITE_ECRITURE'),
                    $this->getActionBoutonConfig('Log', 'LogLister', ['objectClass' => 'TypeActivite', 'objectId' => 'id']),
                ]]);
    }
    public function getEntity()
    {
        return 'UcaBundle\Entity\TypeActivite';
    }

    public function getName()
    {
        return 'TypeActivite_datatable';
    }
}
