<?php

/*
 * classe - FlashBag
 *
 * Service gérant les flashbag (messages d'alerte)
*/

namespace App\Service\Common;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashBag
{
    private $translator;
    private $flashBag;

    public function __construct(TranslatorInterface $translator, FlashBagInterface $flashBag)
    {
        $this->translator = $translator;
        $this->flashBag = $flashBag;
    }

    public function addTranslatedFlashBag($type, $message)
    {
        $translatedMessage = $this->translator->trans($message);
        $this->flashBag->add($type, $translatedMessage);
    }

    public function addFlashBag($item, $message, $bootstrapColor)
    {
        $message = Fctn::getShortClassName($item).'.'.$message;
        $this->addTranslatedFlashBag(strtolower($bootstrapColor), strtolower($message));
    }

    public function addActionFlashBag($item, $action)
    {
        $this->addFlashBag($item, $action.'.succes', 'success');
    }

    public function addActionErrorFlashBag($item, $action)
    {
        $this->addFlashBag($item, $action.'.erreur', 'danger');
    }

    public function addMessageFlashBag($message, $bootstrapColor, $params = [])
    {
        $translatedMessage = $this->translator->trans($message, $params);
        $this->flashBag->add(strtolower($bootstrapColor), ucfirst($translatedMessage));
    }
}
