<?php

namespace UcaBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation\Template;
use UcaBundle\Entity\IntervalleDate;
use Symfony\Component\HttpFoundation\JsonResponse;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\DhtmlxDate;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Ressource;
use UcaBundle\Entity\Tarif;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\Reservabilite;
use UcaBundle\Entity\Traits\JsonSerializable;

class DhtmlxController extends Controller
{
    /**
     * @Route("/DhtmlxApi", methods={"GET"}, name="DhtmlxApi", options={"expose"=true})
     */
    public function getEventAction(Request $request)
    {
        $id = $request->query->get("activite");
        $type = $request->query->get("type");
        $em = $this->getDoctrine()->getManager();

        //get the DhtmlxDate by FormatActivite
        //this return serie and event of the serie

        $series = [];
        if ($type == "ressource" || $type == 'FormatActivite') {
            $events = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxDateByReference($type, $id);
            $series = $em->getRepository(DhtmlxSerie::class)->findDhtmlxDateByReference($type, $id);
        } 
            elseif ($type == "encadrant") {
            $user = $this->getUser();
            if ($user == null) {
                return false;
            }

            $id = $user->getId();

            $events = $em->getRepository(DhtmlxDate::class)->findDhtmlxDateByEncadrant(
                $id
            );
        }

        return new JsonResponse(['evenements' => $events, 'series' => $series]);
    }


    /**
     * @Route("/DhtmlxSendMail", methods={"POST"}, name="DhtmlxSendMail", options={"expose"=true})
     */
    public function sendMail(Request $request)
    {

        $id = $request->request->get("id");
        $text = $request->request->get("text");


        $em = $this->getDoctrine()->getManager();

        //find all user who have subscrive to this event
        $ev = $em->getRepository(DhtmlxEvenement::class)->find($id);

        $c = $ev->getSerie()->getCreneau();
        $inscriptions = $c->getInscriptions();

        $emailToSend = array();
        foreach ($inscriptions as $key => $i) {

            $emailToSend[] = $i->getUtilisateur()->getEmail();
        }

        $message = (new \Swift_Message('Unice - Evenement message'))
            ->setFrom(['acatus.unice@gmail.com' => 'Unice'])
            ->setTo($emailToSend)
            ->setBody($text);

        // Send the message
        $result = $this->get('mailer')
            ->send($message);

        return new JsonResponse(json_encode(array("mesage" => "send")));
    }

    /**
     * @Route("/DhtmlxApi", methods={"POST"})
     */
    public function DhtmlxApiPostAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ev = $request->request->get('evenement');
        $c = new DhtmlxCommand($em, $ev);
        if($ev['action'] == 'delete') {
            $res = $c->getResult();
            $c->execute();
            $em->flush();
        }
        else {
            $c->execute();
            $em->flush();
            $res = $c->getResult();
        }
        return new JsonResponse($res);
    }
}
