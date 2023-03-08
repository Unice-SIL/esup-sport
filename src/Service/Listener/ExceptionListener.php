<?php

/*
 * classe - ExceptionListener
 *
 * Service gérant les redirection des erreurs serveurs
*/

namespace App\Service\Listener;

use App\Exception\ShibbolethException;
use App\Service\Common\FlashBag;
use App\Service\Common\MailService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionListener
{
    private $router;
    private $flashbag;
    private $mailer;
    private $exceptionReceiver;
    private $em;
    private $connection;

    public function __construct(RouterInterface $router, FlashBag $flashbag, MailService $mailer, string $exceptionReceiver, EntityManagerInterface $em, Connection $connection)
    {
        $this->router = $router;
        $this->flashbag = $flashbag;
        $this->mailer = $mailer;
        $this->exceptionReceiver = $exceptionReceiver;
        $this->em = $em;
        $this->connection = $connection;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if ($exception instanceof ShibbolethException) {
            $this->flashbag->addMessageFlashBag($exception->getMessage(), 'danger');
            $event->setResponse(new RedirectResponse($this->router->generate('security_login')));
        } elseif ($exception instanceof NotFoundHttpException && false !== strpos($exception->getMessage(), '/login')) {
            $event->setResponse(new RedirectResponse($this->router->generate('security_login')));
        } elseif (!$exception instanceof NotFoundHttpException && !$exception instanceof AccessDeniedHttpException) {
            if (!empty($this->exceptionReceiver)) {
                // On clear les objets qui sont éventuellement persistés dans l'entité manager (pour éviter de les sauvegarder avec les logs)
                $this->em->clear();

                // Récupération des requêtes exécutées sur la page en cours d'exécution
                $queries = $this->connection->getConfiguration()->getSQLLogger()->queries ?? null;

                $this->mailer->sendMailWithTemplate(
                    'PHP Exception',
                    $this->exceptionReceiver,
                    'UcaBundle/Email/Exception/PhpException.html.twig',
                    [
                        'exception' => $exception,
                        'error_code' => (!empty($queries) ? $exception->getCode() : ''),
                        'last_query' => (!empty($queries) ? end($queries)['sql'] : ''),
                    ],
                    null,
                    true
                );
            }
        }
    }
}
