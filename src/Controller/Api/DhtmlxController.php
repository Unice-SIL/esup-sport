<?php

/*
 * Classe - DataController:
 *
 * Classe liée à librairie DHTMLX
 * Contrôleur deédié à la gestion de la libraire
*/

namespace App\Controller\Api;

use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\DhtmlxDate;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\Inscription;
use App\Repository\DhtmlxEvenementRepository;
use App\Repository\DhtmlxSerieRepository;
use App\Repository\InscriptionRepository;
use App\Service\Common\MailService;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DhtmlxController extends AbstractController
{
    /**
     * @Route("/DhtmlxApi", methods={"GET"}, name="DhtmlxApi", options={"expose"=true})
     */
    public function getEventAction(Request $request, DhtmlxEvenementRepository $eventRepo, DhtmlxSerieRepository $serieRepo)
    {
        $id = $request->query->get('activite');
        $type = $request->query->get('type');

        // get the DhtmlxDate by FormatActivite
        // this return serie and event of the serie

        $series = [];
        $events = [];
        if ('ressource' == $type || 'FormatActivite' == $type) {
            $events = $eventRepo->findDhtmlxDateByReference($type, $id);
            $series = $serieRepo->findDhtmlxDateByReference($type, $id);
            $preInscriptionsReservabilite = null;
        } elseif ('encadrant' == $type || 'user' == $type) {
            $user = $this->getUser();
            if (null == $user) {
                return false;
            }

            $id = $user->getId();

            $InscriptionsCreneaux = $eventRepo->findDhtmlxCreneauByUser($user);
            $EncadrementsCreneaux = $serieRepo->findDhtmlxCreneauByEncadrant($user);
            $InscriptionsReservabilite = $eventRepo->findDhtmlxReservabiliteByUser($user);
            $preInscriptionsReservabilite = $eventRepo->findDhtmlxReservabiliteAttentePartenaireByUser($user);
            $InscriptionsformatSimple = $eventRepo->findDhtmlxFormatSimpleByUser($user);
            $EncadrementsformatSimple = $eventRepo->findDhtmlxFormatSimpleByEncadrant($user);
            // $events = array_merge($creneaux, $reservabilites);

            $events = array_merge($InscriptionsCreneaux, $InscriptionsReservabilite, $InscriptionsformatSimple, $EncadrementsformatSimple);
            $series = array_merge($EncadrementsCreneaux);
        }

        return new JsonResponse(['evenements' => $events, 'series' => $series, 'preinscription' => $preInscriptionsReservabilite]);
    }

    /**
     * @Route("/DhtmlxSendMail", methods={"POST"}, name="DhtmlxSendMail", options={"expose"=true})
     */
    public function sendMail(Request $request, MailService $mailer, DhtmlxEvenementRepository $eventRepo, TranslatorInterface $translator)
    {
        $id = $request->request->get('id');
        $text = $request->request->get('text');

        // find all user who have subscrive to this event
        $ev = $eventRepo->find($id);

        $c = $ev->getSerie()->getCreneau();
        $inscriptions = $c->getInscriptions();

        $emailToSend = [];
        foreach ($inscriptions as $key => $i) {
            $user = $i->getUtilisateur();
            if ($user->getEmail()) {
                $emailToSend[] = $user->getEmail();
            } else {
                $this->addFlash('error', $translator->trans('mail.not_found', ['%user%' => $user->getPrenom().' '.$user->getNom()]));
            }
        }

        $objet = $ev->getFormatActiviteLibelle().' : '.date_format($ev->getDateDebut(), 'Y/m/d H:i:s').' - '.date_format($ev->getDateFin(), 'Y/m/d H:i:s');

        $mailer->sendMailWithTemplate(
            $objet,// Préciser dans le sujet, le titre de l'inscription
            $emailToSend,
            'UcaBundle/Email/PreInscription/MailPourTousLesInscripts.html.twig',// Préciser dans le contenu, le titre de l'inscription
            ['message' => $text]
        );

        return new JsonResponse(json_encode(['mesage' => 'send']));
    }

    /**
     * @Route("/DhtmlxApi", methods={"POST"})
     */
    public function DhtmlxApiPostAction(Request $request, DhtmlxCommand $c, EntityManagerInterface $em)
    {
        $ev = $request->request->get('evenement');
        if (isset($ev['evenementType']) && 'ressource' == $ev['evenementType'] && !isset($ev['enfants'])) {
            $ev['hasSerie'] = 'false';
            $ev['dependanceSerie'] = 'true';
        }

        $c = new DhtmlxCommand();
        $c->init($em, $ev);

        if ('delete' == $ev['action']) {
            $item = $c->getItem();
            if ($item instanceof DhtmlxEvenement) {
                // si on veut supprimer le dernier événement d'une série, on supprime aussi la série pour éviter les problèmes de suppression de format
                if (null !== $item->getSerie() && sizeof($item->getSerie()->getEvenements()) <= 1) {
                    $em->remove($item->getSerie());
                }

                if ($item->getReservabilite() && sizeof($events = $em->getRepository(DhtmlxEvenement::class)->findBy(['reservabilite' => $item->getReservabilite()->getId()]))) {
                    foreach ($events as $event) {
                        if ($event->getId() != $item->getId()) {
                            $em->remove($event);
                        }
                    }
                }
            }
            $res = $c->getResult();
            $c->execute();
            $em->flush();
        } elseif ('extend' == $ev['action']) {
            $newCreneau = [];
            $modelCreneau = $em->getRepository(DhtmlxEvenement::class)->findOneById($ev['id']);
            $modelReservabilite = $modelCreneau->getReservabilite();
            for ($i = 0; $i < $ev['nbRepetition']; ++$i) {
                $creneau = new DhtmlxEvenement();
                $creneau = clone $modelCreneau;
                if ($modelReservabilite) {
                    $reservabilite = clone $modelReservabilite;
                    $em->persist($reservabilite);
                    $creneau->setReservabilite($reservabilite);
                }
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
            $c = new DhtmlxCommand();
            $c->init($em, $ev);
            $c->execute();
            $em->flush();
            $res = $c->getResult();
        }

        return new JsonResponse($res);
    }

    /**
     * @Route("/DhtmlxSerieInscrit", methods={"POST"}, name="DhtmlxSerieInscrit", options={"expose"=true})
     */
    public function isInscritForSerie(Request $request, DhtmlxSerieRepository $serieRepo, InscriptionRepository $inscRepo)
    {
        $serie = $serieRepo->findOneById($request->request->get('id'));

        return new JsonResponse($inscRepo->inscriptionParCreneauStatut($serie->getCreneau(), $request->request->get('statut')));
    }

    /**
     * @Route("/DhtmlxNbOccurrenceDependance", methods={"POST"}, name="DhtmlxNbOccurrenceDependance", options={"expose"=true})
     */
    public function isSeuleOccurrenceDependance(Request $request, DhtmlxEvenementRepository $eventRepo)
    {
        $serie = $request->request->get('serieId');
        $serie = $eventRepo->findBy(['serie' => $serie, 'dependanceSerie' => true]);

        return count($serie) > 1 ? new JsonResponse(false) : new JsonResponse(true);
    }

    /**
     * @Route("/DhtmlxAnnulerInscription", methods={"POST"}, name="DhtmlxAnnulerInscription", options={"expose"=true})
     */
    public function annulerInscription(Request $request, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        $serie = $em->getRepository(DhtmlxSerie::class)->findOneById($request->request->get('id'));
        $listeInscriptionAAnnuler = $em->getRepository(Inscription::class)->findByCreneau($serie->getCreneau());
        foreach ($listeInscriptionAAnnuler as $inscription) {
            $listeCommandeDetail = $em->getRepository(CommandeDetail::class)->findBy(['type' => 'inscription', 'inscription' => $inscription->getId(), 'creneau' => $serie->getCreneau()]);
            foreach ($listeCommandeDetail as $commandeDetail) {
                $em->remove($commandeDetail);
            }
            $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
            $inscription->setCreneau(null);
            $inscription->setStatut('annule', ['motifAnnulation' => 'suppressionserie']);
        }
        $em->flush();

        return new JsonResponse(200);
    }
}
