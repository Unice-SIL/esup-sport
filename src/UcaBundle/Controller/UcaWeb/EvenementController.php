<?php

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
     */
    public function listAction(Request $request, $page)
    {
        if ($page < 1)
            $page = 1;
        $em = $this->getDoctrine()->getManager();

        $evenements = $em->getRepository('UcaBundle:FormatSimple')
            ->PromotionsPagination($page, 5, $this->getUser());

        $pagination = array(
            'page' => $page,
            'nbPages' => ceil(count($evenements) / 5),
            'nomRoute' => 'UcaWeb_Evenement',
            'paramsRoute' => array()
        );

        $twigConfig["pagination"] = $pagination;
        $twigConfig["evenements"] = $evenements;
        return $this->render('@Uca/UcaWeb/Evenement/Lister.html.twig', $twigConfig);
    }
}
