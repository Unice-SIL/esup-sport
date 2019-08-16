<?php

namespace UcaBundle\Service\Common;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use UcaBundle\UcaBundle;

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

    public function dateFormat($date, $type = null)
    {
        // Liste des formats autorisé
        // http://userguide.icu-project.org/formatparse/datetime
        return (new \IntlDateFormatter($this->requestStack->getCurrentRequest()->getLocale(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, null, null, $type))->format($date);
    }
}
