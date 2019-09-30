<?php

namespace UcaBundle\Service\Common;

use UcaBundle\Service\Common\Fn;

class FlashBag
{
    private $translator;
    private $requestStack;

    public function __construct($translator, $requestStack)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
    }

    public function addTranslatedFlashBag($type, $message)
    {
        $fb = $this->requestStack->getCurrentRequest()->getSession()->getFlashBag();
        $translatedMessage = $this->translator->trans($message);
        $fb->add($type, $translatedMessage);
    }

    public function addFlashBag($item, $message, $bootstrapColor)
    {
        $message = Fn::getShortClassName($item) . '.' . $message;
        $this->addTranslatedFlashBag(strtolower($bootstrapColor), strtolower($message));
    }

    public function addActionFlashBag($item, $action)
    {
        $this->addFlashBag($item, $action . '.succes', 'success');
    }

    public function addActionErrorFlashBag($item, $action)
    {
        $this->addFlashBag($item, $action . '.erreur', 'danger');
    }

    public function addMessageFlashBag($message, $bootstrapColor)
    {
        $fb = $this->requestStack->getCurrentRequest()->getSession()->getFlashBag();
        $translatedMessage = $this->translator->trans($message);
        $fb->add(strtolower($bootstrapColor), ucfirst($translatedMessage));
    }
}
