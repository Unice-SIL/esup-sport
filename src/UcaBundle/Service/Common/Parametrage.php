<?php

namespace UcaBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use UcaBundle\Entity\Parametrage as ParametrageEntity;

class Parametrage
{
    public $em;
    public static $object = null;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        self::$object = $this->em->getRepository(ParametrageEntity::class)->find(1);
    }

    public static function get()
    {
        return self::$object;
    }

    public static function timeoutToDateLimit($timer)
    {
        return (new \DateTime())->sub(new \DateInterval('PT' . $timer . 'M'));
    }

    public static function getDateDebutCbLimite()
    {
        return self::timeoutToDateLimit(self::$object->getTimerCb());
    }

    public static function getDateDebutPanierLimite()
    {
        return self::timeoutToDateLimit(self::$object->getTimerPanier());
    }

    public static function getDateDebutBdsLimite()
    {
        return self::timeoutToDateLimit(self::$object->getTimerBds() * 60);
    }

    public static function getDateDebutPanierApresValidationLimite()
    {
        return self::timeoutToDateLimit(self::$object->getTimerPanierApresValidation() * 60);
    }
}
