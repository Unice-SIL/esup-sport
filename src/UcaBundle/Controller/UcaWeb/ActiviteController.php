<?php

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Entity\FormatSimple;
use UcaBundle\Entity\Ressource;

/**
 * @Route("UcaWeb")
 */
class ActiviteController extends Controller
{
    /**
     * @Route("/ClasseActiviteLister", name="UcaWeb_ClasseActiviteLister", methods={"GET"})
     */
    public function ClasseActiviteListerAction()
    {
        $em = $this->getDoctrine()->getManager();
        $twigConfig['entite'] = 'ClasseActivite';
        $twigConfig['data'] = $em->getRepository('UcaBundle:ClasseActivite')->findAll();
        // $twigConfig["item"] = array_chunk($data, round(count($data) / 2, 0, PHP_ROUND_HALF_UP));
        return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/ClasseActivite/{id}/ActiviteLister", name="UcaWeb_ActiviteLister", methods={"GET"})
     *
     * @param mixed $id
     */
    public function ActiviteListerAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $twigConfig['entite'] = 'Activite';
        $twigConfig['item'] = $em->getRepository('UcaBundle:ClasseActivite')->findOneBy(['id' => $id]);
        $twigConfig['data'] = $em->getRepository('UcaBundle:Activite')->findByClassActivite($id, $this->getUser());

        return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
    }

    /**
     * @Route("/ClasseActivite/{idCa}/Activite/{id}/FormatActiviteLister", name="UcaWeb_FormatActiviteLister", methods={"GET"})
     *
     * @param mixed $idCa
     * @param mixed $id
     */
    public function FormatActiviteListerAction($idCa, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $twigConfig['idCa'] = $idCa;
        $twigConfig['entite'] = 'FormatActivite';
        $twigConfig['item'] = $em->getRepository('UcaBundle:Activite')->findOneBy(['id' => $id]);
        $twigConfig['data'] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($twigConfig['item'], $this->getUser());
        $twigConfig['etablissements'] = $em->getRepository('UcaBundle:Etablissement')->findEtablissementByActivite($id);

        return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
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
    public function FormatActiviteDetailAction($idCa, $idA, $id, $day = 1, $yearWeek = null)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository('UcaBundle:FormatActivite')->findOneBy(['id' => $id]);

        $twigConfig['data'] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($idA, $this->getUser());
        $twigConfig['idCa'] = $idCa;
        $twigConfig['idA'] = $idA;
        $twigConfig['item'] = $item;
        $twigConfig['id'] = $item->getId();
        $twigConfig['creneauParJour'] = [];

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
        $twigConfig['data'] = $item->getRessource();

        return $this->render('@Uca/UcaWeb/Activite/ListerRessource.html.twig', $twigConfig);
    }

    public function FormatActiviteAvecCreneau($item, $day, $twigConfig)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $item->getId();
        $twigConfig['entite'] = 'FormatActiviteDetail';

        $events = null;

        $evenements = $em->getRepository('UcaBundle:DhtmlxEvenement')->findPremierEvenementDependantSerieDeChaqueSerieDuFormat($id);
        foreach ($evenements as $event) {
            if (null != $event['dateDebut']) {
                $ev = $event[0];
                $dayOfWeek = $ev->getDateDebut()->format('w');

                if ($dayOfWeek == $day) {
                    $events[$ev->getSerie()->getId()]['creneau'] = $ev;
                    $events[$ev->getSerie()->getId()]['nextCreneauDebut'] = $event['dateDebut'];
                }

                if (empty($twigConfig['nombreEvenement'][$dayOfWeek])) {
                    $twigConfig['nombreEvenement'][$dayOfWeek] = 0;
                }
                ++$twigConfig['nombreEvenement'][$dayOfWeek];
                if ('disponible' == $ev->getSerie()->getCreneau()->getInscriptionInformations($this->getUser(), $item)['statut']) {
                    if (empty($twigConfig['nombreEvenementDispo'][$dayOfWeek])) {
                        $twigConfig['nombreEvenementDispo'][$dayOfWeek] = 0;
                    }
                    ++$twigConfig['nombreEvenementDispo'][$dayOfWeek];
                }
            }
        }

        $twigConfig['days'] = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $twigConfig['currentDay'] = $day;
        $twigConfig['events'] = $events;

        return $this->render('@Uca/UcaWeb/Activite/FormatActivite.html.twig', $twigConfig);
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
    public function FormatActiviteAvecReservation(Request $request, $idCa, $idA, $id, $idRessource, $year_week = null, $day_week = null)
    {
        $em = $this->getDoctrine()->getManager();
        $ressource = $em->getRepository('UcaBundle:Ressource')->findOneBy(['id' => $idRessource]);
        $item = $em->getRepository('UcaBundle:FormatAvecReservation')->findOneBy(['id' => $id]);
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

        $activite = $em->getRepository('UcaBundle:Activite')->findOneBy(['id' => $idA]);

        $twigConfig['data'] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($activite, $this->getUser());
        $yearWeek = $date->format('Y').'W'.$date->format('W');

        $reservabilites = $em->getRepository('UcaBundle:Reservabilite')->findByDhtmlxDateByWeek($ressource->getId(), $yearWeek);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $reservabilitesByDay = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];

        foreach ($reservabilites as $key => $reservabilite) {
            $reservabilitesByDay[$reservabilite->getEvenement()->getDateDebut()->format('w')][] = $reservabilite;
        }

        $dateNextCreneau = null;
        $reservabilitesAVenir = $em->getRepository('UcaBundle:Reservabilite')->findReservabilite($ressource->getId(), $this->findNextDate($date));
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

        return $this->render('@Uca/UcaWeb/Activite/FormatReservation.html.twig', $twigConfig);
    }

    public function FormatActiviteAchatCarte($twigConfig)
    {
        return $this->render('@Uca/UcaWeb/Activite/FormatAchatCarte.html.twig', $twigConfig);
    }

    public function FormatActiviteSimple($twigConfig)
    {
        return $this->render('@Uca/UcaWeb/Activite/FormatSimple.html.twig', $twigConfig);
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
