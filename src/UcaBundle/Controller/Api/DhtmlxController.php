<?php

/*
 * Classe - DataController:
 *
 * Classe liée à librairie DHTMLX
 * Contrôleur deédié à la gestion de la libraire
*/

namespace UcaBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\DhtmlxDate;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Inscription;

class DhtmlxController extends Controller
{
    /**
     * @Route("/DhtmlxApi", methods={"GET"}, name="DhtmlxApi", options={"expose"=true})
     */
    public function getEventAction(Request $request)
    {
        $id = $request->query->get('activite');
        $type = $request->query->get('type');
        $em = $this->getDoctrine()->getManager();

        //get the DhtmlxDate by FormatActivite
        //this return serie and event of the serie

        $series = [];
        $events = [];
        if ('ressource' == $type || 'FormatActivite' == $type) {
            $events = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxDateByReference($type, $id);
            $series = $em->getRepository(DhtmlxSerie::class)->findDhtmlxDateByReference($type, $id);
        } elseif ('encadrant' == $type || 'user' == $type) {
            $user = $this->getUser();
            if (null == $user) {
                return false;
            }

            $id = $user->getId();

            $InscriptionsCreneaux = $em->getRepository(DhtmlxSerie::class)->findDhtmlxCreneauByUser($user);
            $EncadrementsCreneaux = $em->getRepository(DhtmlxSerie::class)->findDhtmlxCreneauByEncadrant($user);
            $InscriptionsReservabilite = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxReservabiliteByUser($user);
            $InscriptionsformatSimple = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxFormatSimpleByUser($user);
            $EncadrementsformatSimple = $em->getRepository(DhtmlxEvenement::class)->findDhtmlxFormatSimpleByEncadrant($user);
            //$events = array_merge($creneaux, $reservabilites);

            $events = array_merge($InscriptionsReservabilite, $InscriptionsformatSimple, $EncadrementsformatSimple);
            $series = array_merge($InscriptionsCreneaux, $EncadrementsCreneaux);
        }

        return new JsonResponse(['evenements' => $events, 'series' => $series]);
    }

    /**
     * @Route("/DhtmlxSendMail", methods={"POST"}, name="DhtmlxSendMail", options={"expose"=true})
     */
    public function sendMail(Request $request)
    {
        $id = $request->request->get('id');
        $text = $request->request->get('text');

        $em = $this->getDoctrine()->getManager();

        //find all user who have subscrive to this event
        $ev = $em->getRepository(DhtmlxEvenement::class)->find($id);

        $c = $ev->getSerie()->getCreneau();
        $inscriptions = $c->getInscriptions();

        $emailToSend = [];
        foreach ($inscriptions as $key => $i) {
            $emailToSend[] = $i->getUtilisateur()->getEmail();
        }

        $mailer = $this->container->get('mailService');
        $mailer->sendMailWithTemplate(
            '',// Préciser dans le sujet, le titre de l'inscription
            $emailToSend,
            '@Uca/Email/PreInscription/MailPourTousLesInscripts.html.twig',// Préciser dans le contenu, le titre de l'inscription
            ['message' => $text]
        );

        return new JsonResponse(json_encode(['mesage' => 'send']));
    }

    /**
     * @Route("/DhtmlxApi", methods={"POST"})
     */
    public function DhtmlxApiPostAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ev = $request->request->get('evenement');
        if (isset($ev['evenementType']) && $ev['evenementType'] == 'ressource' && !isset($ev['enfants'])) {
            $ev['hasSerie'] = 'false';
            $ev['dependanceSerie'] = 'true';
        }
        
        $c = new DhtmlxCommand($em, $ev);
        if ('delete' == $ev['action']) {
            $item = $c->getItem();
            if ($item instanceof DhtmlxEvenement) {
                //si on veut supprimer le dernier événement d'une série, on supprime aussi la série pour éviter les problèmes de suppression de format
                if ($item->getSerie() !== null && sizeof($item->getSerie()->getEvenements()) <= 1) {
                    $em->remove($item->getSerie());
                }
            }
            $res = $c->getResult();
            $c->execute();
            $em->flush();
        } elseif ('extend' == $ev['action']) {
            $newCreneau = [];
            for ($i = 0; $i < $ev['nbRepetition']; ++$i) {
                $creneau = new DhtmlxEvenement();
                $creneau = clone $em->getRepository(DhtmlxEvenement::class)->findOneById($ev['id']);
                $numberWeek = 0;
                $numberWeek = ($i > 0 ? $i : 0);
                $dateRepetition = date('Y-m-d', strtotime('+'.$numberWeek.' week ', strtotime($ev['dateDebutRepetition'])));

                $heureDebutCreneau = date('H:i', strtotime($ev['dateDebut']));
                $heureFinCreneau = date('H:i', strtotime($ev['dateFin']));

                $creneau->setDateDebut(new \DateTime($dateRepetition.' '.$heureDebutCreneau));
                $creneau->setDateFin(new \DateTime($dateRepetition.' '.$heureFinCreneau));
                $em->persist($creneau);
                $newCreneau[] = $creneau;
            }
            $em->flush();

            $res = $creneau->jsonSerialize();

        // return $this->redirectToRoute('DhtmlxApi', ['activite' => $ev['itemId'], 'type' => $ev['typeA']]);
        } else {
            $c = new DhtmlxCommand($em, $ev);
            $c->execute();
            $em->flush();
            $res = $c->getResult();
        }

        return new JsonResponse($res);
    }

    /**
     * @Route("/DhtmlxSerieInscrit", methods={"POST"}, name="DhtmlxSerieInscrit", options={"expose"=true})
     */
    public function isInscritForSerie(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository(DhtmlxSerie::class)->findOneById($request->request->get('id'));

        return new JsonResponse($em->getRepository(Inscription::class)->inscriptionParCreneauStatut($serie->getCreneau(), $request->request->get('statut')));
    }

     /**
     * @Route("/DhtmlxNbOccurrenceDependance", methods={"POST"}, name="DhtmlxNbOccurrenceDependance", options={"expose"=true})
     */
    public function isSeuleOccurrenceDependance(Request $request)
    {
        $serie = $request->request->get('serieId');
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository(DhtmlxEvenement::class)->findBy(['serie' => $serie, 'dependanceSerie' => true]);

        return count($serie) > 1 ? new JsonResponse(false) : new JsonResponse(true);
    }

    /**
     * @Route("/DhtmlxAnnulerInscription", methods={"POST"}, name="DhtmlxAnnulerInscription", options={"expose"=true})
     */
    public function annulerInscription(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serie = $em->getRepository(DhtmlxSerie::class)->findOneById($request->request->get('id'));
        $listeInscriptionAAnnuler = $em->getRepository(Inscription::class)->findByCreneau($serie->getCreneau());
        foreach ($listeInscriptionAAnnuler as $inscription) {
            $listeCommandeDetail = $em->getRepository(CommandeDetail::class)->findBy(['type' => 'inscription', 'inscription' => $inscription->getId(), 'creneau' => $serie->getCreneau()]);
            foreach ($listeCommandeDetail as $commandeDetail) {
                $em->remove($commandeDetail);
            }
            $inscription->updateNbInscrits(false);
            $inscription->setCreneau(null);
            $inscription->setStatut('annule', ['motifAnnulation' => 'suppressionserie']);
        }
        $em->flush();

        return new JsonResponse(200);
    }
}
