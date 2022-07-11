<?php

/*
 * Classe - TwigVirtualColumn
 *
 * Crée une colonnepour le datatable
 * Données et affichage
*/

namespace App\Datatables\Column;

use Sg\DatatablesBundle\Datatable\Column\VirtualColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwigVirtualColumn extends VirtualColumn
{
    private $twigTemplate;
    private $field;

    public function getCellContentTemplate()
    {
        return 'UcaBundle/Datatables/Column/'.$this->twigTemplate.'Column.html.twig';
    }

    public function renderSingleField(array &$row)
    {
        $data = '' != $this->field ? $row[$this->field] : '';
        $twigConfig = [
            'entityClassName' => $this->entityClassName,
            'row' => $row,
            'field' => $this->field,
            'data' => $data,
        ];
        $row[$this->data] = $this->twig->render($this->getCellContentTemplate(), $twigConfig);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'twigTemplate' => 'Twig',
            'field' => '',
        ]);

        return $this;
    }

    public function setTwigTemplate($twigTemplate)
    {
        $this->twigTemplate = $twigTemplate;

        return $this;
    }

    public function getTwigTemplate()
    {
        return $this->twigTemplate;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    public function getField()
    {
        return $this->field;
    }
}
