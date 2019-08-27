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
        try {
            if ($this->get('uca.shibboleth.provider')->isFirstConnection()) {
                return $this->redirectToRoute('UcaWeb_MentionsLegales');
            } else {
                return $this->redirectToRoute('UcaWeb_Accueil');
            }
        } catch (\Exception $e) {
            $this->get('uca.flashbag')->addMessageFlashBag($e->getMessage(), 'danger');
            return $this->redirectToRoute('fos_user_security_login');

        }
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
     * @Route("/UcaWeb/aShibLoginTest", name="UcaWeb_aShibLoginTest")
     */
    public function shibLoginTestAction(Request $request)
    {
        $usp = $this->get('uca.shibboleth.provider');
        $usrConfig = ['dcharlot' => [
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
        ], 'etudiant' => [
            "username" => "99999999@unice.fr",
            "uid" => "99999999",
            "eppn" => "99999999@unice.fr",
            "mail" => "test1.testcrips4@etu.univ-cotedazur.fr",
            "givenName" => "Test1",
            "sn" => "testCRIPS",
            "eduPersonAffiliation" => "member;student",
            "eduPersonPrimaryAffiliation" => "student",
            "supannEtuId" => "99999999",
            "ptdrouv" => "0",
            'supannOrganisme' => '{EES}0060931E'
        ], 'doctor' => [
            "username" => "99999999@unice.fr",
            "uid" => "99999999",
            "eppn" => "99999999@unice.fr",
            "mail" => "test1.testcrips4@etu.univ-cotedazur.fr",
            "givenName" => "Test1",
            "sn" => "testCRIPS",
            "eduPersonAffiliation" => "member;employee;staff;student",
            "eduPersonPrimaryAffiliation" => "student",
            "supannEtuId" => "99999999",
            "ptdrouv" => "0",
            'supannOrganisme' => '{EES}0060931E'
        ]];
        try {
            $usr = $usp->loadUser($usrConfig[$request->get('user')]);
            if ($this->get('uca.shibboleth.provider')->isFirstConnection()) {
                return $this->redirectToRoute('UcaWeb_MentionsLegales');
            } else {
                return $this->redirectToRoute('UcaWeb_Accueil');
            }
        } catch (\Exception $e) {
            $this->get('uca.flashbag')->addMessageFlashBag($e->getMessage(), 'danger');
            return $this->redirectToRoute('fos_user_security_login');
        }
        dump($usr);
        die;
    }
}
