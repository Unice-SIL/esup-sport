<?php

namespace UcaBundle\Service\Common;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use UcaBundle\UcaBundle;
use UcaBundle\Service\Common\Parametrage;

class TwigExtensions extends AbstractExtension
{

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em, \Symfony\Component\HttpFoundation\RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('emplacement', [$this, 'getTexte']),
            new TwigFunction('emplacementImageFond', [$this, 'getImageFond']),
            new TwigFunction('isPrevisualisation', [$this, 'isPrevisualisation']),
            new TwigFunction('parametrage', [$this, 'getParametrage']),
            new TwigFunction('serverPathToWeb', [$this, 'serverPathToWeb']),
            new TwigFunction('urlRetourPrevisualisation', [$this, 'urlRetourPrevisualisation']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('dateFormat', [$this, 'dateFormat']),
        ];
    }

    public function getTexte($name, $type = "text")
    {
        $emplacement = $this->em->getRepository('UcaBundle:Texte')->findOneByEmplacement($name);
        if (is_null($emplacement))
            return "";

        switch ($type) {
            case 'title':
                $text =  $emplacement->getTitre();
                break;

            case "text":
            default:
                $text =  $emplacement->getTexte();
                break;
        }

        return $text;
    }

    public function getImageFond($name)
    {
        return $this->em->getRepository('UcaBundle:ImageFond')->findOneByEmplacement($name);
    }

    public function getParametrage()
    {
        return Parametrage::get();
    }

    public function dateFormat($date, $format = null)
    {
        return Fn::intlDateFormat($date, $format);
    }

    public function isPrevisualisation()
    {
        return Previsualisation::$IS_ACTIVE;
    }

    public function serverPathToWeb()
    {

        $result = $_SERVER['DOCUMENT_ROOT'];
        if (isset($_SERVER['BASE'])) {
            $result .= $_SERVER['BASE'];
        }
        if (substr($result, -1) != '/') {
            $result .= '/';
        }
        return $result;
    }

    public function urlRetourPrevisualisation()
    {
        return Previsualisation::$BACK_URL;
    }
}
