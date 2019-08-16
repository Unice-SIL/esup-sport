<?php
namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/InstallationsSportives")
 */
class InstallationsSportivesController extends Controller
{

    /**
     * @Route("/", name="UcaWeb_InstallationsSportives")
    */
    public function ListerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $etablissements = $em->getRepository('UcaBundle:Etablissement')->findAll();

        return $this->render('@Uca/UcaWeb/InstallationsSportives/Lister.html.twig', array("etablissements" => $etablissements));
    }
}