<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/InfosPratiques")
 */
class InfosPratiquesController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_InfosPratiques")
     */
    public function voirAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $etablissements = $em->getRepository('UcaBundle:Etablissement')->findAll();
        $lieu = $em->getRepository('UcaBundle:Lieu')->findAll();
        $twigConfig['etablissements'] = $etablissements;
        $twigConfig['lieu'] = $lieu;

        return $this->render('@Uca/UcaWeb/InfosPratiques/Voir.html.twig', $twigConfig);
    }
}
