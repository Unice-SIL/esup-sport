<?php

namespace UcaBundle\Service\Common;

class Fn
{
    public static function getFullClassName($item)
    {
        return get_class($item);
    }

    public static function getShortClassName($item)
    {
        $array = explode('\\', self::getFullClassName($item));
        return array_pop($array);
    }
}
