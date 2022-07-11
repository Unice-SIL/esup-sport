<?php

/*
 * Classe - SportHautNiveauController
 *
 * Gère la page de sport de haut niveau (interface web)
 * Permet l'affichage des Highlight du shnu
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\ShnuHighlight;
use App\Entity\Uca\ShnuRubrique;
use App\Repository\LogoPartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("UcaWeb/Sport") */
class SportHautNiveauController extends AbstractController
{
    /**
     * @Route("/", name="UcaWeb_SportVoir")
     */
    public function listerAction(Request $request, EntityManagerInterface $em)
    {
        $twigConfig['liste_rubriques'] = $em->getRepository(ShnuRubrique::class)->findBy(
            [],
            ['ordre' => 'ASC']
        );

        return $this->render('UcaBundle/UcaWeb/SportHautNiveau/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/Highlights/{id}", name="UcaWeb_ShnuHighlights")
     *
     * @param null|mixed $id
     */
    public function voirHighlightsAction(Request $request, ShnuHighlight $premiereVideo, EntityManagerInterface $em)
    {
        $twigConfig['premiere_video'] = $premiereVideo;
        $twigConfig['liste_videos'] = $em->getRepository(ShnuHighlight::class)->findAllExceptFirstChoose($premiereVideo->getId());

        $twigConfig['rubrique'] = $em->getRepository(ShnuRubrique::class)->findOneBy(
            ['type' => 1],
            ['ordre' => 'ASC']
        );

        $twigConfig['routeName_autre_video'] = 'UcaWeb_ShnuHighlights';

        return $this->render('UcaBundle/UcaWeb/Highlight/Voir.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}", name="UcaWeb_ConsulterRubrique")
     */
    public function consulterRubriqueAction(Request $request, ShnuRubrique $rubrique, LogoPartenaireRepository $logoRepo, EntityManagerInterface $em)
    {
        $twigConfig['rubrique'] = $rubrique;

        if (1 == $rubrique->getType()->getId()) {
            $twigConfig['premiere_video'] = null;
            $twigConfig['liste_videos'] = $em->getRepository(ShnuHighlight::class)->findBy(
                [],
                ['ordre' => 'ASC']
            );

            $twigConfig['routeName_autre_video'] = 'UcaWeb_ShnuHighlights';

            return $this->render('UcaBundle/UcaWeb/Highlight/Voir.html.twig', $twigConfig);
        }
        if (2 == $rubrique->getType()->getId()) {
            $logos = $logoRepo->findBy(
                [],
                ['ordre' => 'asc']
            );
            $twigConfig['logos'] = $logos;

            return $this->render('UcaBundle/UcaWeb/SportHautNiveau/Partenaires.html.twig', $twigConfig);
        }
        if (4 == $rubrique->getType()->getId()) {
            return $this->render('UcaBundle/UcaWeb/SportHautNiveau/ConsulterRubrique.html.twig', $twigConfig);
        }

        return new Response('', Response::HTTP_BAD_REQUEST);
    }
}
