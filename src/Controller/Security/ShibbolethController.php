<?php

/*
 * Classe - ShibbolethController
 *
 * Contrôleur technique permettatn l'authentification via shibboleth
*/

namespace App\Controller\Security;

use App\Service\Securite\GestionnaireUtilisateurShibboleth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShibbolethController extends AbstractController
{
    /**
     * @Route("/UcaWeb/ShibLogin", name="UcaWeb_ShibLogin")
     */
    public function shibLoginAction(GestionnaireUtilisateurShibboleth $shibboleth)
    {
        if ($shibboleth->isFirstConnection()) {
            return $this->redirectToRoute('UcaWeb_CGV');
        }

        return $this->redirectToRoute('UcaWeb_Accueil');
    }

    /**
     * @Route("/UcaWeb/AppLogout", name="UcaWeb_AppLogout")
     */
    public function appLogoutAction()
    {
        return new Response();
    }

    /**
     * @Route("/UcaWeb/ShibLogout", name="UcaWeb_ShibLogout")
     */
    public function shibLogoutAction()
    {
        return $this->render('UcaBundle/Common/Securite/Deconnexion.html.twig');
    }
}
