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
    private $mailer;
    private $exceptionReceiver;
    private $em;
    private $connection;

    public function __construct($router, $flashbag, $mailer, $exceptionReceiver, $em, $connection)
    {
        $this->router = $router;
        $this->flashbag = $flashbag;
        $this->mailer = $mailer;
        $this->exceptionReceiver = $exceptionReceiver;
        $this->em = $em;
        $this->connection = $connection;
    }

    public function onKernelException($event)
    {
        $exception = $event->getException();
        if ($exception instanceof ShibbolethException) {
            $this->flashbag->addMessageFlashBag($exception->getMessage(), 'danger');
            $event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
        } else {
            // On clear les objets qui sont éventuellement persistés dans l'entité manager (pour éviter de les sauvegarder avec les logs)
            $this->em->clear();

            // Récupération des requêtes exécutées sur la page en cours d'exécution
            $queries = $this->connection->getConfiguration()->getSQLLogger()->queries ?? null;

            $this->mailer->sendMailWithTemplate(
                'PHP Exception',
                $this->exceptionReceiver,
                '@Uca/Email/Exception/PhpException.html.twig',
                [
                    'exception' => $exception,
                    'error_code' => (!empty($queries) ? $this->connection->errorCode() : ''),
                    'last_query' => (!empty($queries) ? end($queries)['sql'] : ''),
                ]
            );
        }
    }
}
