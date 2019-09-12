<?php

namespace UcaBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ErreurController extends Controller
{
    /**
     * @Route("/UcaWeb/Erreur500", name="UcaWeb_Erreur500", options={"expose"=true})
     */
    public function erreur500Action()
    {
        return $this->render('@TwigBundle/Resources/views/Exception/error500.html.twig');
    }
}
