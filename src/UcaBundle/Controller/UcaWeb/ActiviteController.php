<?php

namespace UcaBundle\Controller\UcaWeb;

use UcaBundle\Entity\Activite;
use UcaBundle\Entity\FormatActivite;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Entity\FormatSimple;

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
    $twigConfig["data"] = $em->getRepository('UcaBundle:Activite')->findBy(['classeActivite' => $twigConfig["item"]]);
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
    $twigConfig["data"] = $em->getRepository('UcaBundle:FormatActivite')->findFormatPublie($twigConfig["item"]);
    return $this->render('@Uca/UcaWeb/Activite/Lister.html.twig', $twigConfig);
  }

  /**
   * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetail/{id}/day/{day}", name="UcaWeb_FormatActiviteDetailJour")
   * @Route("/ClasseActivite/{idCa}/Activite/{idA}/FormatActiviteDetail/{id}", name="UcaWeb_FormatActiviteDetail")
   * @Method("GET")
   */
  public function FormatActiviteDetailAction($idCa, $idA, $id, $day = 1)
  {
    $em = $this->getDoctrine()->getManager();
    $item = $em->getRepository('UcaBundle:FormatActivite')->findOneBy(['id' => $id]);

    if (get_class($item) == FormatAvecCreneau::class) {
      return $this->FormatActiviteAvecCreneau($idCa, $idA, $item, $day);
    } elseif (get_class($item) == FormatAchatCarte::class) {
      return $this->FormatActiviteAchatCarte($id);
    } elseif (get_class($item) == FormatAvecReservation::class) {
      return $this->FormatActiviteAvecReservation($idCa, $idA, $item);
    } elseif (get_class($item) == FormatSimple::class) {
      return $this->FormatActiviteSimple($id);
    }
  }

  function FormatActiviteAvecCreneau($idCa, $idA, $item, $day)
  {
    $em = $this->getDoctrine()->getManager();
    $id = $item->getId();
    $twigConfig["idCa"] = $idCa;
    $twigConfig["idA"] = $idA;
    $twigConfig["item"] = $item;
    $twigConfig["id"] = $item->getId();
    $twigConfig["entite"] = 'FormatActiviteDetail';


    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];



    $twigConfig["Evenements"] = $em->getRepository("UcaBundle:DhtmlxSerie")->findByDhtmlxDateByDay($days[$day - 1], $id);
    $twigConfig["nextCreneaux"] = $em->getRepository("UcaBundle:DhtmlxSerie")->findByDhtmlxDateByDay($days[$day - 1], $id, false);

    $twigConfig["days"] = $days;
    $twigConfig["currentDay"] = $day;
    return $this->render('@Uca/UcaWeb/Activite/FormatActivite.html.twig', $twigConfig);
  }

  public function FormatActiviteAvecReservation($idCa, $idA, $item, $year_week = null)
  {
    $em = $this->getDoctrine()->getManager();
    $ressource = $item->getRessource()[0];
    if(is_null($year_week)){
      $date = new \DateTime('now');
      $nextWeek = new \DateTime('now');
      $nextWeek->modify("+1 week")->format("Y_W");
      $previousWeek = new \DateTime('now');
      $previousWeek->modify("-1 week")->format("Y_W");
      $week = $date->format("W");
    }
    $yearWeek = $date->format("Y")."W".$week;

    $endate = new \DateTime($yearWeek);
    $twigConfig["start_date"] =  new \DateTime($yearWeek);

    $twigConfig["next_date"] = $nextWeek;
    $twigConfig["previous_date"] = $previousWeek;
    
    $twigConfig["item"] = $item;
    $ressources = $em->getRepository("UcaBundle:DhtmlxEvenement")->findByDhtmlxDateByWeek($ressource->getId(),$yearWeek);

    $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    $weekday = date("w");
    $ressourcesSort = array(1 => [], 2 => [],3 => [],4 => [],5 => [],6 => [], 0 => []);
    foreach($ressources as $key => $ressource){
      $ressourcesSort[$ressource->getDateDebut()->format("w")][] = $ressource;
    }
    $twigConfig["ressourcesSort"] = $ressourcesSort;
    $twigConfig["days"] = $days;
    return $this->render('@Uca/UcaWeb/Activite/FormatReservation.html.twig', $twigConfig);
  }

  /**
   * @Route("/FormatActiviteAchatCarte/{id}", name="UcaWeb_FormatActiviteAchatCarte")
   * @Method("GET")
   */
  public function FormatActiviteAchatCarte($id)
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
  public function FormatActiviteSimple($id)
  {
    $em = $this->getDoctrine()->getManager();
    $item = $this->getDoctrine()->getRepository(FormatActivite::class)->findOneById($id);
    $twigConfig["item"] = $item;
    return $this->render('@Uca/UcaWeb/Activite/FormatSimple.html.twig', $twigConfig);
  }
}
