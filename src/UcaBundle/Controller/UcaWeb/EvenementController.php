<?php

/*
 * Classe - EvenementController
 *
 * Liste des évenements
*/

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/Evenement")
 */
class EvenementController extends Controller
{
    /**
     * @Route("/{page}", name="UcaWeb_Evenement", defaults={"page" = 1})
     *
     * @param mixed $page
     */
    public function listAction(Request $request, $page)
    {
        if ($page < 1) {
            $page = 1;
        }
        $em = $this->getDoctrine()->getManager();

        $evenements = $em->getRepository('UcaBundle:FormatSimple')
            ->PromotionsPagination($page, 5)
        ;

        $pagination = [
            'page' => $page,
            'nbPages' => ceil(count($evenements) / 5),
            'nomRoute' => 'UcaWeb_Evenement',
            'paramsRoute' => [],
        ];

        $twigConfig['pagination'] = $pagination;
        $twigConfig['evenements'] = $evenements;

        return $this->render('@Uca/UcaWeb/Evenement/Lister.html.twig', $twigConfig);
    }
}
