<?php

/*
 * Classe - SportHautNiveauController
 *
 * Gère la page de sport de haut niveau (interface web)
 * Permet l'affichage des Highlight du shnu
*/

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\LogoPartenaire;
use UcaBundle\Entity\ShnuHighlight;

/** @Route("UcaWeb/Sport") */
class SportHautNiveauController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_SportVoir")
     */
    public function listerAction(Request $request)
    {
        return $this->render('@Uca/UcaWeb/SportHautNiveau/Voir.html.twig');
    }

    /**
     * @Route("/Accompagnements", name="UcaWeb_Accompagnements")
     */
    public function voirAccompagnementsAction(Request $request)
    {
        return $this->render('@Uca/UcaWeb/SportHautNiveau/Accompagnements.html.twig');
    }

    /**
     * @Route("/Highlights/{id}", name="UcaWeb_ShnuHighlights")
     *
     * @param null|mixed $id
     */
    public function voirHighlightsAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

        $premiereVideo = null;
        if (null != $id) {
            $premiereVideo = $em->getRepository(ShnuHighlight::class)->findOneBy(['id' => $id]);
        }

        $twigConfig['premiere_video'] = $premiereVideo;
        if (null != $premiereVideo) {
            $twigConfig['liste_videos'] = $em->getRepository(ShnuHighlight::class)->findAllExceptFirstChoose($id);
        } else {
            $twigConfig['liste_videos'] = $em->getRepository(ShnuHighlight::class)->findBy(
                [],
                ['ordre' => 'ASC']
            );
        }

        $twigConfig['routeName_autre_video'] = 'UcaWeb_ShnuHighlights';

        return $this->render('@Uca/UcaWeb/Highlight/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/Representer", name="UcaWeb_Representer")
     */
    public function voirRepresenterAction(Request $request)
    {
        return $this->render('@Uca/UcaWeb/SportHautNiveau/Representer.html.twig');
    }

    /**
     * @Route("/Partenaires", name="UcaWeb_Partenaires")
     */
    public function voirPartenairesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $logos = $em->getRepository(LogoPartenaire::class)->findBy(
            [],
            ['ordre' => 'asc']
        );
        $twigConfig['logos'] = $logos;

        return $this->render('@Uca/UcaWeb/SportHautNiveau/Partenaires.html.twig', $twigConfig);
    }
}
