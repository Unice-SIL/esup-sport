<?php

namespace UcaBundle\Controller\UcaWeb;

use UcaBundle\Entity\Activite;
use UcaBundle\Entity\FormatActivite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
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
   * @Route("/ClasseActiviteLister", name="UcaWeb_ClasseActiviteLister")
   * @Method("GET")
   */
  public function ClasseActiviteListerAction()
  {
    $em = $this->getDoctrine()->getManager();
    $twigConfig["entite"] = 'ClasseActivite';
    $twigConfig["data"] = $em->getRepository('UcaBundle:ClasseActivite')->findAll();
    // $twigConfig["item"] = array_chunk($data, round(count($data) / 2, 0, PHP_ROUND_HALF_UP));
    return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
  }

  /**
   * @Route("/ClasseActivite/{id}/ActiviteLister", name="UcaWeb_ActiviteLister")
   * @Method("GET")
   */
  public function ActiviteListerAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    $twigConfig["entite"] = 'Activite';
    $twigConfig["item"] = $em->getRepository('UcaBundle:ClasseActivite')->findOneBy(['id' => $id]);
    $twigConfig["data"] = $em->getRepository('UcaBundle:Activite')->findByClassActivite($id, $this->getUser());
    return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
  }

  /**
   * @Route("/ClasseActivite/{idCa}/Activite/{id}/FormatActiviteLister", name="UcaWeb_FormatActiviteLister")
   * @Method("GET")
   */
  public function FormatActiviteListerAction($idCa, $id)
  {
    $em = $this->getDoctrine()->getManager();
    $twigConfig["idCa"] = $idCa;
    $twigConfig["entite"] = 'FormatActivite';
    $twigConfig["item"] = $em->getRepository('UcaBundle:Activite')->findOneBy(['id' => $id]);
    $twigConfig["data"] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($twigConfig["item"], $this->getUser());
    $twigConfig["etablissements"] = $em->getRepository('UcaBundle:Etablissement')->findEtablissementByActivite($id);
    return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
  }

  /**
   * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetail/{id}/day/{day}", name="UcaWeb_FormatActiviteDetailJour")
   * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetail/{id}", name="UcaWeb_FormatActiviteDetail")
   * @Method("GET")
   * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
   */
  public function FormatActiviteDetailAction($idCa, $idA, $id, $day = 1, $yearWeek = null)
  {
    $em = $this->getDoctrine()->getManager();
    $item = $em->getRepository('UcaBundle:FormatActivite')->findOneBy(['id' => $id]);

    $activite = $em->getRepository('UcaBundle:Activite')->findOneBy(['id' => $idA]);

    $twigConfig["data"] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($activite, $this->getUser());
    $twigConfig["idCa"] = $idCa;
    $twigConfig["idA"] = $idA;
    $twigConfig["item"] = $item;
    $twigConfig["id"] = $item->getId();

    if (get_class($item) == FormatAvecCreneau::class) {
      return $this->FormatActiviteAvecCreneau($idCa, $idA, $item, $day, $twigConfig);
    } elseif (get_class($item) == FormatAchatCarte::class) {
      return $this->FormatActiviteAchatCarte($id, $twigConfig);
    } elseif (get_class($item) == FormatAvecReservation::class) {
      return $this->formatAvecReservationVoirRessource($idCa, $idA, $item, $yearWeek, $twigConfig);
    } elseif (get_class($item) == FormatSimple::class) {
      return $this->FormatActiviteSimple($id, $twigConfig);
    }
  }

  public function formatAvecReservationVoirRessource($idCa, $idA, $item, $yearWeek, $twigConfig)
  {
    $em = $this->getDoctrine()->getManager();

    $twigConfig["data"] = $item->getRessource();

    return $this->render('@Uca/UcaWeb/Activite/ListerRessource.html.twig', $twigConfig);
  }

  function FormatActiviteAvecCreneau($idCa, $idA, $item, $day, $twigConfig)
  {
    $em = $this->getDoctrine()->getManager();
    $id = $item->getId();
    $twigConfig["idCa"] = $idCa;
    $twigConfig["idA"] = $idA;
    $twigConfig["item"] = $item;
    $twigConfig["id"] = $item->getId();
    $twigConfig["entite"] = 'FormatActiviteDetail';

    $date = new \DateTime();

    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

    $evenements = $em->getRepository("UcaBundle:DhtmlxSerie")->findByDhtmlxDateByDay($days[$day - 1], $id);
    $nextCreneaux = $em->getRepository("UcaBundle:DhtmlxSerie")->findByDhtmlxDateByDay($days[$day - 1], $id, false, $date);

    $events = array();

    //add crenrau and nextcreneau together
    foreach($evenements as $ev){
      $events[$ev->getSerie()->getId()]["creneau"] = $ev;
    }
    foreach($nextCreneaux as $c){
      $events[$c->getSerie()->getId()]["nextCreneau"] = $c;
    }

    $twigConfig["days"] = $days;
    $twigConfig["currentDay"] = $day;
    $twigConfig["events"] = $events;
    return $this->render('@Uca/UcaWeb/Activite/FormatActivite.html.twig', $twigConfig);
  }

  /**
   * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetailReservation/{id}/ressource/{idRessource}", name="UcaWeb_FormatActiviteReservationDetail")
   * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetailReservation/{id}/ressource/{idRessource}/yearWeek/{year_week}", name="UcaWeb_FormatActiviteReservationDetailAnneeSemaine")
   * @Method("GET") 
   */
  public function FormatActiviteAvecReservation(Request $request, $idCa, $idA, $id, $idRessource, $year_week = null)
  {
    $em = $this->getDoctrine()->getManager();
    $ressource = $em->getRepository('UcaBundle:Ressource')->findOneBy(['id' => $idRessource]);
    $item = $em->getRepository('UcaBundle:FormatAvecReservation')->findOneBy(['id' => $id]);

    $twigConfig["idRessource"] = $idRessource;
    $twigConfig["libelleRessource"] = $ressource->getLibelle();
    $twigConfig["idCa"] = $idCa;
    $twigConfig["idA"] = $idA;
    $twigConfig["item"] = $item;
    $twigConfig["id"] = $item->getId();
    if (is_null($year_week)) {
      $date = new \DateTime('now');
    } else {
      $dateYW = explode("_", $year_week);
      $date = new \DateTime($dateYW[0] . "W" . $dateYW[1]);
    }



    $activite = $em->getRepository('UcaBundle:Activite')->findOneBy(['id' => $idA]);

    $twigConfig["data"] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($activite, $this->getUser());
    $yearWeek = $date->format("Y") . "W" . $date->format("W");


    $reservabilites = $em->getRepository("UcaBundle:Reservabilite")->findByDhtmlxDateByWeek($ressource->getId(), $yearWeek);

    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    $reservabilitesByDay = array(1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 0 => []);

    foreach ($reservabilites as $key => $reservabilite) {
      $reservabilitesByDay[$reservabilite->getEvenement()->getDateDebut()->format("w")][] = $reservabilite;
    }

    $twigConfig["reservabilitesByDay"] = $reservabilitesByDay;
    $twigConfig["days"] = $days;
    $twigConfig["start_date"] =  new \DateTime($yearWeek);
    $twigConfig["next_date"] = $this->findNextDate($date);
    $twigConfig["previous_date"] = $this->findPreviousDate($date);
    $twigConfig["beginning"] = $request->query->get('beginning');
    return $this->render('@Uca/UcaWeb/Activite/FormatReservation.html.twig', $twigConfig);
  }

  private function findNextDate(\DateTime $date)
  {

    $week = $date->format("W");
    $nextWeek = clone $date;
    $nextWeek->modify("+1 week");

    //On test si le changement d'annee tombe au mileu de la semaine 52
    //Si c'est le cas on change nous même la date
    if ($week == 52 && $nextWeek->format("W") != "53") {
      $isSameYear = $date->format("Y") == $nextWeek->format("Y") ? true : false;
      if ($isSameYear) {
        $nw = explode("_", $nextWeek->format("Y_W"));
        $nextWeek = ($nw[0] + 1) . "_01";
      }
    }

    return $nextWeek->format("Y_W");
  }

  private function findPreviousDate(\DateTime $date)
  {
    $previousWeek = clone $date;

    return $previousWeek->modify("-1 week")->format("Y_W");
  }


  /**
   * @Route("/FormatActiviteAchatCarte/{id}", name="UcaWeb_FormatActiviteAchatCarte")
   * @Method("GET")
   */
  public function FormatActiviteAchatCarte($id, $twigConfig)
  {
    $em = $this->getDoctrine()->getManager();
    $item = $this->getDoctrine()->getRepository(FormatActivite::class)->findOneById($id);
    $twigConfig["item"] = $item;
    return $this->render('@Uca/UcaWeb/Activite/FormatAchatCarte.html.twig', $twigConfig);
  }

  /**
   * @Route("/FormatActiviteSimple/{id}", name="UcaWeb_FormatActiviteSimple")
   * @Method("GET")
   */
  public function FormatActiviteSimple($id, $twigConfig)
  {
    $em = $this->getDoctrine()->getManager();
    $item = $this->getDoctrine()->getRepository(FormatActivite::class)->findOneById($id);
    $twigConfig["item"] = $item;
    return $this->render('@Uca/UcaWeb/Activite/FormatSimple.html.twig', $twigConfig);
  }
}
