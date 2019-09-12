<?php

namespace UcaBundle\Service\Listener;

class DoctrineExtensionListener
{
    protected $translatableListener;
    protected $loggableListener;
    protected $tokenStorage;
    protected $authoriaztionChecker;

    public function setTranslatableListener(\Gedmo\Translatable\TranslatableListener $translatableListener = null)
    {
        $this->translatableListener = $translatableListener;
    }

    public function setLoggableListener(\Gedmo\Loggable\LoggableListener $loggableListener = null)
    {
        $this->loggableListener = $loggableListener;
    }

    public function setTokenStorage(\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setAuthoriaztionChecker(\Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authoriaztionChecker = null)
    {
        $this->authoriaztionChecker = $authoriaztionChecker;
    }

    public function onLateKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
        $locale = $event->getRequest()->getLocale();
        $this->translatableListener->setTranslatableLocale($locale);
        ini_set('intl.default_locale', $locale);
    }
    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event)
    {
        $tokenStorage = $this->tokenStorage->getToken();
        $authorizationChecker = $this->authoriaztionChecker;
        if (null !== $tokenStorage && $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggable = $this->loggableListener;
            $loggable->setUsername($tokenStorage->getUser());
            // $blameable = $this->container->get('gedmo.listener.blameable');
            // $blameable->setUserValue($tokenStorage->getUser());
        }
    }
}

