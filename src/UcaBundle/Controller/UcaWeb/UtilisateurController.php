<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Utilisateur;

/**
 * @Route("UcaWeb/Utilisateur")
 */
class UtilisateurController extends Controller
{
    /**
     * @Route("/{id}",name="UcaWeb_UtilisateurVoir")
    */
    public function voirAction(Request $request, Utilisateur $item)
    {
        $em = $this->getDoctrine()->getManager();
        $twigConfig['type'] = "encadrant";
        $twigConfig['role'] = "encadrant";
        $twigConfig['item'] = $item;
        $twigConfig['activiteSouscrite'] = $this->FormatParActivite($item);
        return $this->render('@Uca/UcaWeb/Utilisateur/Voir.html.twig', $twigConfig);
    }

    public function FormatParActivite(Utilisateur $item)
    {
        $listeActivite = [];
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQuery('SELECT fa FROM UcaBundle\Entity\FormatActivite fa JOIN fa.inscriptions i');
        $formatsActivites = $qb->getResult();
        foreach ($formatsActivites as $format){
                $listeActivite[$format->getActivite()->getLibelle()][] = $format->getLibelle();
        }
        return $listeActivite;
    }
}