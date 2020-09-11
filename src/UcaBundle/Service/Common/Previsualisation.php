<?php

/*
 * classe - Previsalisation
 *
 * Service gérant la prévisulisation des contenus
*/

namespace UcaBundle\Service\Common;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class Previsualisation
{
    public static $IS_ACTIVE = false;
    public static $BACK_URL = '';

    private $authorizationChecker;
    private $event;

    public function __construct(TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function clearSession()
    {
        $this->event->getRequest()->getSession()->remove('previsualisation');
        $this->event->getRequest()->getSession()->remove('urlRetourPrevisualisation');
    }

    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
        if (null == $this->tokenStorage->getToken()) {
            return;
        }
        $this->event = $event;

        //Check if the user have the role previsualisation and delete session var if is not the case
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->clearSession();

            return;
        }

        if (!$this->tokenStorage->getToken()->getUser()->hasRole('ROLE_PREVISUALISATION')) {
            $this->clearSession();

            return;
        }

        $previsualisation = $event->getRequest()->get('previsualisation');
        if (in_array($previsualisation, ['on', 'off'])) {
            $event->getRequest()->getSession()->set('previsualisation', $previsualisation);
        }

        $urlRetourPrevisualisation = $event->getRequest()->get('urlRetourPrevisualisation');
        if (!empty($urlRetourPrevisualisation)) {
            $event->getRequest()->getSession()->set('urlRetourPrevisualisation', $urlRetourPrevisualisation);
        }

        $previsualisation = $event->getRequest()->getSession()->get('previsualisation');
        $urlRetourPrevisualisation = $event->getRequest()->getSession()->get('urlRetourPrevisualisation');
        self::$IS_ACTIVE = 'on' == $previsualisation;

        if (!self::$IS_ACTIVE) {
            $this->clearSession();
        } else {
            self::$BACK_URL = empty($urlRetourPrevisualisation) ? '?previsualisation=off' : $urlRetourPrevisualisation;
        }
    }
}
