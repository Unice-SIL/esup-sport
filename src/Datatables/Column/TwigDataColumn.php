<?php

/*
 * Classe - TwigDataColumn
 *
 * Crée une colonne pour le datatable
 * Données uniquement
*/

namespace App\Datatables\Column;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Helper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwigDataColumn extends Column
{
    private $twigTemplate;

    public function getCellContentTemplate()
    {
        return 'UcaBundle/Datatables/Column/'.$this->twigTemplate.'Column.html.twig';
    }

    public function renderSingleField(array &$row)
    {
        $path = Helper::getDataPropertyPath($this->data);
        $data = $this->accessor->getValue($row, $path);
        $twigConfig = [
            'entityClassName' => $this->entityClassName,
            'propertyPath' => trim($path, '[]'),
            'row' => $row,
            'data' => $data,
        ];
        $row[$this->data] = $this->twig->render($this->getCellContentTemplate(), $twigConfig);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'twigTemplate' => 'Twig',
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
}
