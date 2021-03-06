<?php

/*
 * Classe - StatistiqueController
 *
 * Gestion du formulaire de personnalisation
*/

namespace UcaBundle\Controller\UcaGest\Gestion;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Form\StatistiqueType;

/**
 * @Route("UcaGest/Statistique")
 * @Isgranted("ROLE_GESTION_EXTRACTION")
 */
class StatistiqueController extends Controller
{
    /**
     * @Route("/",name="UcaGest_Statistique_KPI")
     */
    public function voirAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $extraction = $this->get('uca.extraction.inscription');
        $form = $this->get('form.factory')->create(StatistiqueType::class, null, $extraction->getOptionsInscription());
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Reporting/Statistiques/Voir.html.twig', $twigConfig);
    }
}
