<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Panier;

/**
 * @Route("UcaWeb")
 */
class PanierController extends Controller
{
    /**
     * @Route("/Panier", name="UcaWeb_Panier")
     */
    public function voirAction(Request $request)
    {
        $utilisateur = $this->getUser();
        /* Si utilisateur authentifié */
        if ($utilisateur) {
            $em = $this->getDoctrine()->getManager();
            $panier = $utilisateur->getPanier();
            if ($panier == null) {
                $panier = new Panier($utilisateur);
                $em->persist($panier);
                $em->flush();
            }
            $twigConfig["panier"] = $panier;
            return $this->render('@Uca/UcaWeb/Panier/Voir.html.twig', $twigConfig);
        }
        /* Sinon on redirige vers page de connexion */
        else return $this->redirectToRoute('fos_user_security_login');
    }
}
