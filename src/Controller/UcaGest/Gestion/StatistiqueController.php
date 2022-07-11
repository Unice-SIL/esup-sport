<?php

/*
 * Classe - StatistiqueController
 *
 * Gestion du formulaire de personnalisation
*/

namespace App\Controller\UcaGest\Gestion;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\StatistiqueType;
use App\Service\Service\ExtractionInscriptionService;

/**
 * @Route("UcaGest/Statistique")
 * @Isgranted("ROLE_GESTION_EXTRACTION")
 */
class StatistiqueController extends AbstractController
{
    /**
     * @Route("/",name="UcaGest_Statistique_KPI")
     */
    public function voirAction(Request $request, ExtractionInscriptionService $extraction)
    {
        $form = $this->createForm(StatistiqueType::class, null, $extraction->getOptionsInscription());
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Reporting/Statistiques/Voir.html.twig', $twigConfig);
    }
}
