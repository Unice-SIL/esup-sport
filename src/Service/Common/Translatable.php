<?php

/*
 * classe - Translatable
 *
 * Service gérant les traduction et la langue du site
*/

namespace App\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Translatable
{
    private $em;
    private $requestStack;

    private $locale;
    private $traductions = [];

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function getTranslation($params)
    {
        $this->init();
        $res = array_filter($this->traductions, function ($item) use ($params) {
            return
                $item->getLocale() == $this->locale &&
                $item->getObjectClass() == $params['objectClass'] &&
                $item->getField() == $params['field'] &&
                $item->getForeignKey() == $params['foreignKey'];
        });
        if (!empty($res)) {
            $res = reset($res);
            $res = $res->getContent();
        }

        return empty($res) ? $params['data'] : $res;
    }

    private function init()
    {
        if (empty($this->locale)) {
            $this->locale = $this->requestStack->getCurrentRequest()->getLocale();
            $this->traductions = $this->em->getRepository(\Gedmo\Translatable\Entity\Translation::class)->findAll();
        }
    }
}
