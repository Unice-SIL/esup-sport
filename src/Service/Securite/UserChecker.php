<?php

namespace App\Service\Securite;

use App\Entity\Uca\Utilisateur;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private $flashBagInterface;
    private $routerInterface;

    public function __construct(FlashBagInterface $flashBagInterface, RouterInterface $routerInterface)
    {
        $this->flashBagInterface = $flashBagInterface;
        $this->routerInterface = $routerInterface;
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        if (in_array('ROLE_BLOQUE', $user->getRoles())) {
            throw new CustomUserMessageAuthenticationException('app.flash_message.error.user_lock');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        if (in_array('ROLE_BLOQUE', $user->getRoles())) {
            throw new CustomUserMessageAuthenticationException('app.flash_message.error.user_lock');
        }
    }
}