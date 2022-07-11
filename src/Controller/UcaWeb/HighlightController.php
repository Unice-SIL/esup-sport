<?php

/*
 * Classe - HighlightController
 *
 * Gestion de l'affichage des Highlights.
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Highlight;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @Route("UcaWeb/Highlights") */
class HighlightController extends AbstractController
{
    /**
     * @Route("/{id}", name="UcaWeb_Highlights")
     *
     * @param null|mixed $id
     */
    public function voirHighlightsAction(Request $request, $id = null, EntityManagerInterface $em)
    {
        $premiereVideo = null;
        if (null != $id) {
            $premiereVideo = $em->getRepository(Highlight::class)->findOneBy(['id' => $id]);
        }

        $twigConfig['premiere_video'] = $premiereVideo;
        if (null != $premiereVideo) {
            $twigConfig['liste_videos'] = $em->getRepository(Highlight::class)->findFirstExceptFirstChoose($id);
        } else {
            $twigConfig['liste_videos'] = $em->getRepository(Highlight::class)->findBy(
                [],
                ['ordre' => 'ASC']
            );
        }

        $twigConfig['routeName_autre_video'] = 'UcaWeb_Highlights';

        return $this->render('UcaBundle/UcaWeb/Highlight/Voir.html.twig', $twigConfig);
    }
}
