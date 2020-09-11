<?php

/*
 * classe - GroupEventListener
 *
 * Service surchargant certaines méthodes de FoSUser
*/

namespace UcaBundle\Service\Listener\Securite\Groupe;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GroupEventListener implements EventSubscriberInterface
{
    private $router;
    private $flashMessage;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::GROUP_EDIT_SUCCESS => 'onGroupEdit',
            FOSUserEvents::GROUP_CREATE_SUCCESS => 'onGroupCreate',
        ];
    }

    public function onGroupEdit(FormEvent $event)
    {
        $url = $this->router->generate('UcaGest_GroupeLister');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onGroupCreate(FormEvent $event)
    {
        $url = $this->router->generate('UcaGest_GroupeLister');
        $event->setResponse(new RedirectResponse($url));
    }
}
