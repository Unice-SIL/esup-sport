<?php

namespace UcaBundle\Datatables;

use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\BooleanColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;

class TexteDatatable extends AbstractTranslatedDatatable
{
    public function buildDatatable(array $options = [])
    {
        $this->setUcaDefault();

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                'visible' => false,
            ))
            ->add('emplacement', Column::class, array(
                'title' => $this->translator->trans('common.emplacement'),
                'visible' => true,
            ))
            ->add('titre', Column::class, array(
                'title' => $this->translator->trans('common.titre'),
            ))
            ->add('texte', Column::class, array(
                'title' => $this->translator->trans('common.texte'),
            ))
            ->add('mobile', BooleanColumn::class, array(
                'title' => $this->translator->trans('common.mobile'),
                'visible' => false
            ))
            ->add(null, ActionColumn::class, [
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' =>  [
                    $this->getActionBoutonConfig('Modifier', 'TexteModifier', ['id' => 'id'], 'ROLE_GESTION_TEXTE_ECRITURE')
                ]
            ]);
    }

    public function getEntity()
    {
        return 'UcaBundle\Entity\Texte';
    }

    public function getName()
    {
        return 'texte_datatable';
    }
}
