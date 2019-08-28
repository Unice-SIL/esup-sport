<?php

namespace UcaBundle\Service\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use UcaBundle\Exception\ShibbolethException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListener
{
    private $router;
    private $flashbag;

    public function __construct($router, $flashbag)
    {
        $this->router = $router;
        $this->flashbag = $flashbag;
    }

    public function onKernelException($event)
    {
        $exception = $event->getException();
        if ($exception instanceof ShibbolethException) {
            $this->flashbag->addMessageFlashBag($exception->getMessage(), 'danger');
            $event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
        }
    }
}