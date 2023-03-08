<?php

/*
 * Classe - ActiviteSearchController
 *
 * Pertmet de rechercher les activités pour un intervalle d'heures et de de date
*/

namespace App\Controller\UcaWeb;

use App\Form\RechercheDhtmlxEvenementType;
use App\Repository\ActiviteRepository;
use App\Repository\FormatAvecCreneauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/ActiviteSearch")
 */
class ActiviteSearchController extends AbstractController
{
    /**
     * @Route("/", name="UcaWeb_ActiviteSearch", methods={"GET", "POST"})
     */
    public function activiteSearch(Request $request, ActiviteRepository $aRepo, FormatAvecCreneauRepository $faRepo, EntityManagerInterface $em)
    {
        $twigConfig['activites'] = [];
        $twigConfig['formatsActivite'] = [];
        $form = $this->createForm(RechercheDhtmlxEvenementType::class, ['em' => $em]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            $activites = $aRepo->findActiviteByDateTimePeriod($data['weekday'], $data['interval_time_start'], $data['interval_time_end'], $data['etablissement']);
            $twigConfig['activites'] = $activites;
            foreach ($activites as $activite) {
                $twigConfig['formatsActivite'][$activite->getId()] = $faRepo->findFormatActiviteByDateTimePeriodAndActivite($data['weekday'], $data['interval_time_start'], $data['interval_time_end'], $activite, $data['etablissement']);
            }

        }

        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaWeb/ActiviteSearch/Lister.html.twig', $twigConfig);
    }
}
