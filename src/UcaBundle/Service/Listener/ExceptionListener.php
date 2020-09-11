<?php

/*
 * classe - ExceptionListener
 *
 * Service gérant les redirection des erreurs serveurs
*/

namespace UcaBundle\Service\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use UcaBundle\Exception\ShibbolethException;

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
