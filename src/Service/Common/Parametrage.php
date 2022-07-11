<?php

/*
 * classe - Paramêtrage
 *
 * Service gérant les paramêtres globaux de l'application
*/

namespace App\Service\Common;

use App\Repository\ParametrageRepository;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class Parametrage
{
    public $parametrageRepository;
    public static $object;

    public function __construct(ParametrageRepository $parametrageRepository)
    {
        $this->parametrageRepository = $parametrageRepository;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        self::$object = $this->parametrageRepository->find(1);
    }

    public static function get()
    {
        return self::$object;
    }

    public static function timeoutToDateLimit($timer)
    {
        return (new \DateTime())->sub(new \DateInterval('PT'.$timer.'M'));
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

    public static function getDateDebutInscriptionPartenaires()
    {
        return (new \DateTime())->sub(new \DateInterval('PT'.self::$object->getTimerPartenaire().'H'));
    }

    public static function getMailContact()
    {
        return self::$object->getMailContact();
    }
}