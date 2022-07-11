<?php

/*
 * Classe - ActiviteController
 *
 * Gestion des activités  et formats d'activités pour les inscriptions
 * Affichage des activités disponibles
 * Gestion des types et classes d'activité.
*/

namespace App\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Uca\Activite;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Ressource;
use App\Form\RechercheActiviteType;
use App\Repository\ActiviteRepository;
use App\Repository\ClasseActiviteRepository;
use App\Repository\EtablissementRepository;
use App\Repository\FormatActiviteRepository;
use App\Repository\FormatAvecReservationRepository;
use App\Repository\ReservabiliteRepository;
use App\Repository\RessourceRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("UcaWeb")
 */
class ActiviteController extends AbstractController
{
    /**
     * @Route("/ClasseActiviteLister", name="UcaWeb_ClasseActiviteLister", methods={"GET", "POST"})
     */
    public function ClasseActiviteListerAction(Request $request, ClasseActiviteRepository $caRepo, ActiviteRepository $aRepo, EntityManagerInterface $em)
    {
        $twigConfig['entite'] = 'ClasseActivite';
        $twigConfig['data'] = $caRepo->findAll();
        $twigConfig['activites'] = [];
        $form = $this->createForm(RechercheActiviteType::class, ['em' => $em]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $twigConfig['entite'] = 'Activite';
            $twigConfig['data'] = [];
            $twigConfig['activites'] = $aRepo->findRecherche($form->getData()['activite'], $form->getData()['etablissement']);
        }

        $twigConfig['form'] = $form->createView();
        // $twigConfig["item"] = array_chunk($data, round(count($data) / 2, 0, PHP_ROUND_HALF_UP));
        return $this->render('UcaBundle/UcaWeb/Activite/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/ClasseActivite/{id}/ActiviteLister", name="UcaWeb_ActiviteLister", methods={"GET"})
     *
     * @param mixed $id
     */
    public function ActiviteListerAction($id, ClasseActiviteRepository $caRepo, ActiviteRepository $aRepo)
    {
        if ($item = $caRepo->find($id)) {
            $twigConfig['entite'] = 'Activite';
            $twigConfig['item'] = $item;
            $twigConfig['data'] = $aRepo->findByClassActivite($id, $this->getUser());

            return $this->render('UcaBundle/UcaWeb/Activite/Lister.html.twig', $twigConfig);
        }

        $this->addFlash('danger', 'activite.not.found');

        return $this->redirectToRoute('UcaWeb_ClasseActiviteLister');
    }

    /**
     * @Route("/ClasseActivite/{idCa}/Activite/{id}/FormatActiviteLister", name="UcaWeb_FormatActiviteLister", methods={"GET"})
     *
     * @param mixed $idCa
     * @param mixed $id
     */
    public function FormatActiviteListerAction($idCa, $id,  ActiviteRepository $aRepo, FormatActiviteRepository $faRepo, EtablissementRepository $eRepo)
    {
        if ($item = $aRepo->find($id)) {
            $twigConfig['idCa'] = $idCa;
            $twigConfig['entite'] = 'FormatActivite';
            $twigConfig['item'] = $item;
            $formats = $faRepo->findFormatPublie($twigConfig['item'], $this->getUser());
            //On check les formats du type FormatAvecReservation pour voir si les ressources à réserver ont des créneaux
            //Si elles n'en ont pas on ne les affiches pas
            // foreach ($formats as $key => $format) {
            //     if ($format instanceof FormatAvecReservation) {
            //         $nbRessourceValid = 0;
            //         foreach ($format->getRessource() as $ressource) {
            //             if (sizeof($ressource->getReservabilites()) > 0) {
            //                 ++$nbRessourceValid;
            //             }
            //         }
            //         if (0 == $nbRessourceValid) {
            //             unset($formats[$key]);
            //         }
            //     }
            // }
            $twigConfig['data'] = $formats;
            $twigConfig['etablissements'] = $eRepo->findEtablissementByActivite($id);

            return $this->render('UcaBundle/UcaWeb/Activite/Lister.html.twig', $twigConfig);
        }
        $this->addFlash('danger', 'formatactivite.not.found');

        return $this->redirectToRoute('UcaWeb_ClasseActiviteLister');
    }

    /**
     * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetail/{id}/day/{day}", name="UcaWeb_FormatActiviteDetailJour", methods={"GET"})
     * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetail/{id}", name="UcaWeb_FormatActiviteDetail", methods={"GET"})
     *
     * @param mixed      $idCa
     * @param mixed      $idA
     * @param mixed      $id
     * @param mixed      $day
     * @param null|mixed $yearWeek
     */
    public function FormatActiviteDetailAction($idCa, $idA, $id, $day = 1, $yearWeek = null, EntityManagerInterface $em, FormatActiviteRepository $faRepo)
    {
        $item = $em->getReference(FormatActivite::class, $id);
        $twigConfig = [
            'data' => $faRepo->findFormatPublie($idA, $this->getUser()),
            'idCa' => $idCa,
            'idA' => $idA,
            'item' => $item,
            'id' => $item->getId(),
            'creneauParJour' => [],
        ];

        if (FormatAvecCreneau::class == get_class($item)) {
            return $this->FormatActiviteAvecCreneau($item, $day, $twigConfig);
        }
        if (FormatAchatCarte::class == get_class($item)) {
            return $this->FormatActiviteAchatCarte($twigConfig);
        }
        if (FormatAvecReservation::class == get_class($item)) {
            return $this->formatAvecReservationVoirRessource($item, $twigConfig);
        }
        if (FormatSimple::class == get_class($item)) {
            return $this->FormatActiviteSimple($twigConfig);
        }
    }

    public function formatAvecReservationVoirRessource($item, $twigConfig)
    {
        $ressources = $item->getRessource();
        // foreach ($ressources as $key => $ressource) {
        //     if (0 == sizeof($ressource->getReservabilites())) {
        //         unset($ressources[$key]);
        //     }
        // }
        $twigConfig['data'] = $ressources;

        return $this->render('UcaBundle/UcaWeb/Activite/ListerRessource.html.twig', $twigConfig);
    }

    public function FormatActiviteAvecCreneau($item, $day, $twigConfig)
    {
        // $em = $this->getDoctrine()->getManager();
        $twigConfig = array_merge($twigConfig, [
            'entite' => 'FormatActiviteDetail',
            'item' => $item,
            'itemId' => $item->getId(),
            'typeVisualisation' => 'mois',
            // 'listeCampus' => $em->getRepository(Etablissement::class)->findAll(),
            'currentDate' => new \DateTime(),
            'typeFormat' => 'FormatAvecCreneau',
            // 'widthWindow' => '1350',
            // 'nbJour' => '7',
            'idRessource' => 0,
        ]);

        // $dates = [];
        // for ($d = 1; $d <= 7; ++$d) {
        //     $dt = new \DateTime();
        //     $dt->setISODate($dt->format('o'), $dt->format('W'), $d);
        //     $dates[] = $dt;
        // }
        // $twigConfig['listeJours'] = $dates;

        // $twigConfig['dataCalendrier'] = array_fill(0, count($twigConfig['listeCampus']), array_fill(0, count($twigConfig['listeJours']), null));

        return $this->render('UcaBundle/UcaWeb/Activite/FormatActivite.html.twig', $twigConfig);
    }

    /**
     * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetailReservation/{id}/ressource/{idRessource}", name="UcaWeb_FormatActiviteReservationDetailRessource", methods={"GET"})
     *
     * @param mixed $id
     * @param mixed $idCa
     * @param mixed $idA
     * @param mixed $idRessource
     */
    public function FormatActiviteAvecReservationDetailsRessource($idCa, $idA, $id, $idRessource, FormatActiviteRepository $faRepo, RessourceRepository $rRepo, ActiviteRepository $aRepo, EtablissementRepository $eRepo)
    {
        $item = $faRepo->findOneBy(['id' => $id]);
        $ressource = $rRepo->findOneBy(['id' => $idRessource]);

        $twigConfig['item'] = $item;
        $twigConfig['entite'] = 'FormatActiviteDetail';
        $twigConfig['idCa'] = $idCa;
        $twigConfig['idA'] = $idA;
        // $twigConfig['item'] = $item;
        $twigConfig['id'] = $item->getId();
        $twigConfig['idRessource'] = $idRessource;
        $twigConfig['libelleRessource'] = $ressource->getLibelle();

        $activite = $aRepo->findOneBy(['id' => $idA]);

        $twigConfig['data'] = $faRepo->findFormatPublie($activite, $this->getUser());

        $twigConfig['itemId'] = $id;
        $twigConfig['typeVisualisation'] = 'mois';
        // $twigConfig['listeCampus'] = $eRepo->findAll();
        $twigConfig['currentDate'] = new \DateTime();
        $twigConfig['typeFormat'] = 'FormatAvecReservation';
        // $twigConfig['widthWindow'] = '1350';
        // $twigConfig['nbJour'] = '7';

        // $dates = [];
        // for ($d = 1; $d <= 7; ++$d) {
        //     $dt = new \DateTime();
        //     $dt->setISODate($dt->format('o'), $dt->format('W'), $d);
        //     $dates[] = $dt;
        // }
        // $twigConfig['listeJours'] = $dates;

        // $twigConfig['dataCalendrier'] = array_fill(0, count($twigConfig['listeCampus']), array_fill(0, count($twigConfig['listeJours']), null));

        return $this->render('UcaBundle/UcaWeb/Activite/FormatActivite.html.twig', $twigConfig);
    }

    /**
     * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetailReservation/{id}/ressource/{idRessource}", name="UcaWeb_FormatActiviteReservationDetail", methods={"GET"})
     * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetailReservation/{id}/ressource/{idRessource}/yearWeek/{year_week}", name="UcaWeb_FormatActiviteReservationDetailAnneeSemaine", methods={"GET"})
     * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetailReservation/{id}/ressource/{idRessource}/yearWeek/{year_week}/dayWeek/{day_week}", name="UcaWeb_FormatActiviteReservationDetailAnneeSemaineJour", methods={"GET"})
     *
     * @param mixed      $idCa
     * @param mixed      $idA
     * @param mixed      $id
     * @param mixed      $idRessource
     * @param null|mixed $year_week
     * @param null|mixed $day_week
     */
    public function FormatActiviteAvecReservation(Request $request, $idCa, $idA, $id, $idRessource, $year_week = null, $day_week = null, RessourceRepository $rRepo, FormatAvecReservationRepository $farRepo, ActiviteRepository $aRepo, FormatActiviteRepository $faRepo, ReservabiliteRepository $resRepo)
    {
        $ressource = $rRepo->findOneBy(['id' => $idRessource]);
        $item = $farRepo->findOneBy(['id' => $id]);
        $twigConfig['idRessource'] = $idRessource;
        $twigConfig['libelleRessource'] = $ressource->getLibelle();
        $twigConfig['idCa'] = $idCa;
        $twigConfig['idA'] = $idA;
        $twigConfig['item'] = $item;
        $twigConfig['id'] = $item->getId();
        if (is_null($year_week)) {
            $date = new \DateTime('now');
        } else {
            $dateYW = explode('_', $year_week);
            $date = new \DateTime($dateYW[0].'W'.$dateYW[1]);
        }

        $activite = $aRepo->findOneBy(['id' => $idA]);

        $twigConfig['data'] = $faRepo->findFormatPublie($activite, $this->getUser());
        $yearWeek = $date->format('Y').'W'.$date->format('W');

        $reservabilites = $resRepo->findByDhtmlxDateByWeek($ressource->getId(), $yearWeek);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $reservabilitesByDay = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];

        foreach ($reservabilites as $key => $reservabilite) {
            $reservabilitesByDay[$reservabilite->getEvenement()->getDateDebut()->format('w')][] = $reservabilite;
        }

        $dateNextCreneau = null;
        $reservabilitesAVenir = $resRepo->findReservabilite($ressource->getId(), $this->findNextDate($date));
        if ($reservabilitesAVenir) {
            $dateNextCreneau = $reservabilitesAVenir[0]->getEvenement()->getDateDebut();
        }

        $twigConfig['reservabilitesByDay'] = $reservabilitesByDay;
        $twigConfig['days'] = $days;
        $twigConfig['start_date'] = new \DateTime($yearWeek);
        $twigConfig['next_date'] = $this->findNextDate($date);
        $twigConfig['previous_date'] = $this->findPreviousDate($date);
        $twigConfig['beginning'] = $request->query->get('beginning');
        $twigConfig['nextCreneau'] = $dateNextCreneau;

        return $this->render('UcaBundle/UcaWeb/Activite/FormatReservation.html.twig', $twigConfig);
    }

    public function FormatActiviteAchatCarte($twigConfig)
    {
        return $this->render('UcaBundle/UcaWeb/Activite/FormatAchatCarte.html.twig', $twigConfig);
    }

    public function FormatActiviteSimple($twigConfig)
    {
        return $this->render('UcaBundle/UcaWeb/Activite/FormatSimple.html.twig', $twigConfig);
    }

    private function findNextDate(\DateTime $baseDate)
    {
        $date = clone $baseDate;

        return $date->modify('+1 week');
    }

    private function findPreviousDate(\DateTime $baseDate)
    {
        $date = clone $baseDate;

        return $date->modify('-1 week');
    }
}
