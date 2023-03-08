<?php

/*
 * classe - ExceptionListener
 *
 * Service gérant les redirection des erreurs serveurs
*/

namespace App\Service\Listener;

use App\Service\Common\SiteMap;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SiteMapListener
{
    protected $authorizationChecker;
    protected $siteMap;
    protected $translator;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, SiteMap $siteMap, TranslatorInterface $translator)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->siteMap = $siteMap;
        $this->translator = $translator;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController(); 
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest()->get('_route');
        // $droit = ($this->siteMap->get()[$request]['droit']);
        // Verification que la route existe dans le sitemap (conflit profiler etc...)
        $droit = array_key_exists($request, $this->siteMap->get()) ? $this->siteMap->get()[$request]['droit'] : '';
        // Si droit access_denied alors on vérifie que le user a ce droit (qu'il n'aura évidemment pas) pour éviter d'accéder aux routes désactivées
        if ($droit == "ACCESS_DENIED" && !$this->authorizationChecker->isGranted($droit)) {
            throw new AccessDeniedException($this->translator->trans('erreur.403.message'));
        }       
    }
}
