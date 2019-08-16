<?php

namespace UcaBundle\Service\Common;

use UcaBundle\Entity\FormatActivite;
use Gedmo\Loggable\LoggableListener;


class Tools
{
    /**
     * getClassName
     * Retourne le nom complet de la classe en paramètre
     *
     * @param  string $entityName
     * @param  string $type
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
        $className = "UcaBundle" . "\\" . $params[$type]['prefixe'] . "\\" . $entityName . $params[$type]['suffixe'];
        return $className;
    }
    public function getClass($item, $type = 'FormType')
    {
        $res = explode('\\', get_class($item));
        return $this->getClassName(array_pop($res), $type);
    }

    public function getActiviteFormat($item)
    {
        if (is_array($item)) {
            if (isset($item['format']))
                return $item['format'];
        } else if ($item instanceof FormatActivite) {
            $res = explode('\\', get_class($item));
            return array_pop($res);
        }
    }

    public function toString($item)
    {
        if (is_object($item) && get_class($item) == 'DateTime' && $item->format('Y-m-d') == '1970-01-01') return $item->format('H:i');
        if (is_object($item) && get_class($item) == 'DateTime' && $item->format('H:i') == '00:00') return $item->format('d/m/Y');
        if (is_object($item) && get_class($item) == 'DateTime') return $item->format('Y-m-d H:i');
        elseif (is_object($item) && method_exists($item, '__toString')) return $item;
        elseif (is_object($item)) return 'Object';
        elseif (is_array($item)) return 'Array';
        else return $item;
    }
}
