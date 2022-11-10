<?php

/*
 * Classe - AccueilController
 *
 * Gestion de l'acceuil de l'interface de gestion
*/

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    /**
     * @Route("/UcaGest/Accueil", name="UcaGest_Accueil")
     */
    public function accueilAction(Request $request)
    {
        return $this->render('UcaBundle/Common/Main/Accueil.html.twig');
    }
}
