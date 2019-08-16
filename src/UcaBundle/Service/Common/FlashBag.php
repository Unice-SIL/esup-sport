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

    public function addFlashBag($item, $message, $bootstrapColor)
    {
        $fb = $this->requestStack->getCurrentRequest()->getSession()->getFlashBag();
        $translatedMessage = $this->translator->trans(Fn::getShortClassName($item) . '.' . $message);
        $fb->add(strtolower($bootstrapColor), strtolower($translatedMessage));
    }

    public function addActionFlashBag($item, $action)
    {
        $this->addFlashBag($item, $action . '.succes', $action == 'Supprimer' ? 'warning' : 'success');
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
