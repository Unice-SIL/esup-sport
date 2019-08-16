<?php

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UcaBundle\Entity\Article;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\TypeAutorisation;

/**
 * @Route("UcaWeb")
 */
class InscriptionController extends Controller
{
    /**
     * @Route("/Inscription", name="UcaWeb_Inscription", options={"expose"=true})
     * @Method("POST")
     */
    public function Inscription(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get("id");
        $type = $request->get("type");
        $item = $em->getRepository($type)->find($id);
        $ctrl = $this->executeControles($item);

        if ($ctrl['statut'] == 'KO') {
            return new JsonResponse($ctrl);
        } elseif ($ctrl['statut'] == 'Autorisations manquantes') {

            $ctrl['articles'] = [];

            $panier = $this->getUser()->getPanier();
            $article = new Article($panier, $type, $item, $this->getUser());
            $em->persist($article);
            $em->flush();
            array_push($ctrl['articles'], $article);
            return new JsonResponse($ctrl);
        } elseif ($ctrl['statut'] == 'OK') {

            $ctrl['articles'] = [];
            $panier = $this->getUser()->getPanier();
            $article = new Article($panier, $type, $item, $this->getUser());
            $em->persist($article);
            $em->flush();
            array_push($ctrl['articles'], $article);
            return new JsonResponse($ctrl);
        }
    }

    // public function getColumnFilter($item)
    // {
    //     if (is_a($item, FormatActivite::class)) {
    //         return ['formatsActivite' => $item];
    //     } else if (is_a($item, Creneau::class)) {
    //         return ['formatActivite' => $item->getFormatActivite()];
    //     } elseif (is_a($item, TypeAutorisation::class)) {
    //         return ['typeAutorisation' => $item];
    //     }
    // }

    public function executeControles($item)
    {
        $res = [];
        $em = $this->getDoctrine()->getManager();
        $autorisations = $item->getAutorisations();
        foreach ($autorisations as $autorisation) {
            if ($autorisation->getComportement()->getCodeComportement() == 'cotisation') {
                // 
            } elseif ($autorisation->getComportement()->getCodeComportement() == 'justificatif') {
                //
            } elseif ($autorisation->getComportement()->getCodeComportement() == 'case') {
                // 
            } elseif ($autorisation->getComportement()->getCodeComportement() == 'carte') {
                // 
            } elseif ($autorisation->getComportement()->getCodeComportement() == 'validation_encadrant') {
                //
            } else {
                dump($autorisation);
                die;
            }
        }
        return ['statut' => 'OK', 'informations' => []];
    }
}
