<?php

/*
 * classe - Annotation
 *
 * Service gérant les outils pour remonter les informaitons que l'on souhaite
*/

namespace UcaBundle\Service\Common;

use UcaBundle\Entity\FormatActivite;

class Tools
{
    /**
     * getClassName
     * Retourne le nom complet de la classe en paramètre.
     *
     * @param string $entityName
     * @param string $type
     *
     * @return string
     */
    public function getClassName($entityName, $type = 'Entity')
    {
        $params = [
            'Entity' => [
                'prefixe' => 'Entity',
                'suffixe' => '',
            ],
            'FormType' => [
                'prefixe' => 'Form',
                'suffixe' => 'Type',
            ],
            'FormEditType' => [
                'prefixe' => 'Form',
                'suffixe' => 'EditType',
            ],
            'ListType' => [
                'prefixe' => 'ListType',
                'suffixe' => 'ListType',
            ],
        ];

        return 'UcaBundle'.'\\'.$params[$type]['prefixe'].'\\'.$entityName.$params[$type]['suffixe'];
    }

    public function getClass($item, $type = 'FormType')
    {
        $res = explode('\\', get_class($item));

        return $this->getClassName(array_pop($res), $type);
    }

    public function getActiviteFormat($item)
    {
        if (is_array($item)) {
            if (isset($item['format'])) {
                return $item['format'];
            }
        } elseif ($item instanceof FormatActivite) {
            $res = explode('\\', get_class($item));

            return array_pop($res);
        }
    }

    public function toString($item)
    {
        if (is_object($item) && 'DateTime' == get_class($item) && '1970-01-01' == $item->format('Y-m-d')) {
            return $item->format('H:i');
        }
        if (is_object($item) && 'DateTime' == get_class($item) && '00:00' == $item->format('H:i')) {
            return $item->format('d/m/Y');
        }
        if (is_object($item) && 'DateTime' == get_class($item)) {
            return $item->format('Y-m-d H:i');
        }
        if (is_object($item) && method_exists($item, '__toString')) {
            return $item;
        }
        if (is_object($item)) {
            return 'Object';
        }
        if (is_array($item)) {
            return 'Array';
        }

        return $item;
    }
}
