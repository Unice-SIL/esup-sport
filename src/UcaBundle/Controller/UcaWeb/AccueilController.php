<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_Accueil")
     */
    public function accueilAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $twigConfig['item_format_activite'] = $em->getRepository('UcaBundle:FormatSimple')->findByPromouvoir();

        $twigConfig['item_class_activite'] = $em->getRepository('UcaBundle:ClasseActivite')->findAll();
        $twigConfig['item_actualite'] = $em->getRepository('UcaBundle:Actualite')->findBy(
            [],
            ['ordre' => 'ASC']
        );

        return $this->render('@Uca/UcaWeb/Accueil/Main.html.twig', $twigConfig);
    }
}
