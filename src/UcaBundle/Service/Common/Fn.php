<?php

/*
 * classe - Fn
 *
 * Service récupérant les noms d'entités
*/

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

    public static function strTruncate($str, $length)
    {
        return strlen($str) > $length ? mb_substr($str, 0, $length).'...' : $str;
    }

    public static function intlDateFormat($date, $format)
    {
        // Liste des formats autorisé
        // http://userguide.icu-project.org/formatparse/datetime
        return (new \IntlDateFormatter(null, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, null, null, $format))->format($date);
    }
}
