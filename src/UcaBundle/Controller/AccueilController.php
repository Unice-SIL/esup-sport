<?php

/*
 * Classe - AccueilController
 *
 * Gestion de l'acceuil de l'interface de gestion
*/

namespace UcaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends Controller
{
    /**
     * @Route("/UcaGest/Accueil", name="UcaGest_Accueil")
     */
    public function accueilAction(Request $request)
    {
        return $this->render('@Uca/Common/Main/Accueil.html.twig');
    }
}
