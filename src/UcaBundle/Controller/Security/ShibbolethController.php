<?php

namespace UcaBundle\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ShibbolethController extends Controller
{
    /**
     * @Route("/UcaWeb/ShibLogin", name="UcaWeb_ShibLogin")
     */
    public function shibLoginAction()
    {
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
        return $this->render('@Uca/Common/Securite/Deconnexion.html.twig');
    }

    /**
     * @Route("/UcaWeb/ShibLoginTest", name="UcaWeb_ShibLoginTest")
     */
    public function shibLoginTestAction()
    {
        $usp = $this->get('uca.shibboleth.provider');
        $usr = $usp->loadUser([
            "username" => "dcharlot@unice.fr",
            "uid" => "dcharlot",
            "eppn" => "dcharlot@unice.fr",
            "mail" => "Daniel.CHARLOT@univ-cotedazur.fr",
            "givenName" => "Daniel",
            "sn" => "Charlot",
            "eduPersonAffiliation" => "member;staff;employee;teacher",
            "eduPersonPrimaryAffiliation" => "staff",
            "supannEtuId" => "20052308",
            "ptdrouv" => ""
        ]);
    }
}
