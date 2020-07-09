<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Highlight;

/** @Route("UcaWeb/Highlights") */
class HighlightController extends Controller
{
    /**
     * @Route("/{id}", name="UcaWeb_Highlights")
     *
     * @param null|mixed $id
     */
    public function voirHighlightsAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

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

        return $this->render('@Uca/UcaWeb/Highlight/Voir.html.twig', $twigConfig);
    }
}
