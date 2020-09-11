<?php

/*
 * Classe - AccueilController
 *
 * Gestion de l'acceuil de l'application (public)
 * Gestion de la page de connexion
*/

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Service\Service\SelectionProfil;

class AccueilController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_Accueil", methods={"GET","HEAD"}))
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

    /**
     * @Route("/Connexion/SelectionProfil", name="UcaWeb_ConnexionSelectionProfil", methods={"GET","HEAD"})
     */
    public function selectionProfilAction(Request $request)
    {
        $selectionProfil = $this->container->get('uca.selection.profil');
        $twigConfig['selectionProfil'] = $selectionProfil;

        return $this->render('@Uca/UcaWeb/Utilisateur/LoginSelectionProfil.html.twig', $twigConfig);
    }
}
