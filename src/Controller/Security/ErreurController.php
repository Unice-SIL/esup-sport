<?php

/*
 * Classe - ErreurController
 *
 * Contrôleur technqiue : redirection des erreurs
*/

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ErreurController extends AbstractController
{
    /**
     * @Route("/UcaWeb/Erreur500", name="UcaWeb_Erreur500", options={"expose"=true})
     */
    public function erreur500Action()
    {
        return $this->render('TwigBundle/views/Exception/error500.html.twig');
    }
}
