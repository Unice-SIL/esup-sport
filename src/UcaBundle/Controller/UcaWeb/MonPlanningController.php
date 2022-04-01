<?php

/*
 * Classe - MonPlanningController
 *
 * Gère la page Mon planning
 * Lié à la librairie Dhtmlx
 * Selon le profil d'utilisateur les informations ne seront pas les mêmes
*/

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Appel;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Form\EvenementType;
use UcaBundle\Form\PlanningMailType;

/**
 * @Route("UcaWeb/MonPlanning")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MonPlanningController extends Controller
{
    /**
     * @Route("/",name="UcaWeb_MonPlanning")
     */
    public function listerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $this->getUser();
        $isEncadrant = $item->hasRole('ROLE_ENCADRANT');

        $twigConfig['item'] = $item;
        $twigConfig['type'] = 'encadrant';
        if ($isEncadrant) {
            $twigConfig['role'] = 'encadrant';
        } else {
            $twigConfig['role'] = 'user';
        }

        return $this->render('@Uca/UcaWeb/Utilisateur/MonPlanning.html.twig', $twigConfig);
    }

    /**
     * @Route("/more/{id}",name="UcaWeb_PlanningMore")
     * @Route("/more/",name="UcaWeb_PlanningMore_NoId")
     */
    public function voirAction(Request $request, DhtmlxEvenement $dhtmlxEvenement)
    {
        $twigConfig = [];
        $inscriptions = [];
        $em = $this->getDoctrine()->getManager();
        $eventName = '';
        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getAllInscriptions();
                $eventName = $dhtmlxEvenement->getSerie()->getCreneau()->getFormatActivite()->getActivite()->getLibelle();

                if (!$this->getUser()->isEncadrantEvenement($dhtmlxEvenement)) {
                    if (!$this->isGranted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE') && empty($em->getRepository(Inscription::class)->findBy(['creneau' => $dhtmlxEvenement->getSerie()->getCreneau(), 'utilisateur' => $this->getUser()->getId()]))) {
                        return $this->redirectToRoute('UcaWeb_MonPlanning');
                    }
                }
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getAllInscriptions();
            $eventName = $dhtmlxEvenement->getFormatSimple()->getActivite()->getLibelle();
        }

        if (null !== ($reservabilite = $dhtmlxEvenement->getReservabilite()) || (null !== $dhtmlxEvenement->getSerie() && null !== ($reservabilite = $dhtmlxEvenement->getSerie()->getReservabilite()))) {
            $eventName = $reservabilite->getRessource()->getLibelle();
            $inscriptions = $reservabilite->getInscriptions();
        }

        $destinataires = [];
        $existingAppel = $em->getRepository(Utilisateur::class)->findUtilisateurByEvenement($dhtmlxEvenement->getId());
        foreach ($inscriptions as $key => $inscription) {
            if (!in_array($inscription->getUtilisateur(), $existingAppel)) {
                $appel = new Appel();
                $appel->setUtilisateur($inscription->getUtilisateur());
                $appel->setDhtmlxEvenement($dhtmlxEvenement);
                $dhtmlxEvenement->addAppel($appel);
            }
            $user = $inscription->getUtilisateur();
            $key = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
            $destinataires[$key] = $user->getEmail();
        }
        $form = $this->get('form.factory')->create(EvenementType::class, $dhtmlxEvenement);
        $formMail = $this->get('form.factory')->create(PlanningMailType::class, null, ['liste_destinataires' => $destinataires]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($dhtmlxEvenement);
            $em->flush();
        }
        $twigConfig['evenement'] = $dhtmlxEvenement;
        $twigConfig['eventName'] = $eventName;
        $twigConfig['isEncadrant'] = $this->getUser()->isEncadrantEvenement($dhtmlxEvenement);
        $twigConfig['inscriptions'] = $inscriptions;
        $twigConfig['form'] = $form->createView();
        $twigConfig['formMail'] = $formMail->createView();

        return $this->render('@Uca/UcaWeb/Utilisateur/More.html.twig', $twigConfig);
    }

    /**
     * @Route("/mail/{id}", name="UcaWeb_PlanningMore_mail")
     */
    public function sendMail(Request $request, DhtmlxEvenement $dhtmlxEvenement)
    {
        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getAllInscriptions();
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getAllInscriptions();
        }
        $destinataires = [];
        $translator = $this->get('translator');
        foreach ($inscriptions as $key => $inscription) {
            $user = $inscription->getUtilisateur();
            if ($user->getEmail()) {
                $key = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
                $destinataires[$key] = $user->getEmail();
            } else {
                $this->addFlash('error', $translator->trans('mail.not_found', ['%user%' => $user->getPrenom().' '.$user->getNom()]));
            }
        }

        $formMail = $this->get('form.factory')->create(PlanningMailType::class, null, ['liste_destinataires' => $destinataires]);
        $formMail->handleRequest($request);
        $validation = $formMail->isValid();
        if ($request->isMethod('POST') && $validation) {
            $message = $formMail->getData()['mail'];
            $objet = $dhtmlxEvenement->getFormatActiviteLibelle().' : '.date_format($dhtmlxEvenement->getDateDebut(), 'Y/m/d H:i:s').' - '.date_format($dhtmlxEvenement->getDateFin(), 'Y/m/d H:i:s');
            $objet .= ' '.$formMail->getData()['objet'];
            $setTo = $formMail->getData()['destinataires'];
            $copie = $this->getUser()->getEmail();

            $mailer = $this->container->get('mailService');
            $mailer->sendMailWithTemplate(
                $objet,
                $setTo,
                '@Uca/Email/Calendrier/MailPourTousLesInscripts.html.twig',
                ['message' => $message],
                $copie
            );

            return new JsonResponse([
                'sucess' => true,
            ]);
        }

        return new JsonResponse([
            'sucess' => false,
            'form' => $this->renderView('@Uca/UcaWeb/Utilisateur/Modal.mail.form.html.twig', [
                'formMail' => $formMail->createView(),
            ]),
        ]);
    }

    /**
     * @Route("/ListePDF/{id}", name="UcaWeb_PlanningMore_listePdf")
     */
    public function listPdf(Request $request, DhtmlxEvenement $dhtmlxEvenement)
    {
        $inscriptions = [];
        $eventName = '';

        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getAllInscriptions();
                $eventName = $dhtmlxEvenement->getSerie()->getCreneau()->getFormatActivite()->getActivite()->getLibelle();
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getAllInscriptions();
            $eventName = $dhtmlxEvenement->getFormatSimple()->getActivite()->getLibelle();
        }

        if (null != $inscriptions) {
            $content = $this->renderView('@Uca/UcaWeb/Utilisateur/ListeInscriptions.html.twig', ['appels' => $inscriptions, 'evenement' => $dhtmlxEvenement, 'eventName' => $eventName]);

            try {
                $pdf = new HTML2PDF('p', 'A4', 'fr');
                $pdf->pdf->SetAuthor('Université de Nice');
                $pdf->pdf->SetTitle('Listing');
                $pdf->writeHTML($content);
                $pdf->Output('Listing.pdf');
            } catch (HTML2PDF_exception $e) {
                exit($e);
            }

            return new Response();
        }

        return $this->redirectToRoute('UcaWeb_MonPlanning');
    }

    /**
     * @Route("/ListeExcel/{id}", name="UcaWeb_PlanningMore_listeExcel")
     */
    public function listExcel(Request $request, DhtmlxEvenement $dhtmlxEvenement)
    {
        return $this->get('uca.extraction.excel')->getExtractionListeInscription($dhtmlxEvenement);
    }

    /**
     * @Route("/Desinscrire/{user}/{evenement}", name="UcaWeb_PlanningMore_Desinscrire")
     *
     * @param mixed $user
     * @param mixed $evenement
     */
    public function desinscrire(Request $request, $user, $evenement)
    {
        if (null != $user && null != $evenement) {
            $em = $this->getDoctrine()->getManager();
            $event = $em->getRepository('UcaBundle:DhtmlxDate')->find($evenement);
            $participants = [];
            if (!is_null($event->getSerie())) {
                $participants = $em->getRepository('UcaBundle:Inscription')->findUtilisateurPourDesinscriptionCreneau($event->getSerie()->getCreneau()->getId(), $user);
            } elseif (!is_null($event->getFormatSimple())) {
                $participants = $em->getRepository('UcaBundle:Inscription')->findUtilisateurPourDesinscriptionFormat($event->getFormatSimple()->getId(), $user);
            }

            foreach ($participants as $participant) {
                if (in_array($participant->getStatut(), ['initialise', 'attentevalidationencadrant', 'attentevalidationgestionnaire', 'attenteajoutpanier', 'attentepaiement', 'valide'])) {
                    $inscriptionService = $this->get('uca.inscription');
                    $inscriptionService->setInscription($participant);
                    $inscriptionService->mailDesinscription($participant);

                    $em = $this->getDoctrine()->getManager();
                    $participant->seDesinscrire($this->getUser());
                    $em->flush();
                    $appel = $em->getRepository(Appel::class)->findOneBy(['utilisateur' => $user, 'dhtmlxEvenement' => $evenement]);
                    if ($appel) {
                        $em->remove($appel);
                        $em->flush();
                    }
                    // $this->forward('UcaBundle\Controller\UcaWeb\MesInscriptionsController::seDesinscrireAction', ['request' => $request, 'inscription' => $participant]);
                }
            }
        }

        return $this->redirectToRoute('UcaWeb_PlanningMore', ['id' => $evenement]);
    }

    /**
     * @Route("/DesinscrireTout/{id}", name="UcaWeb_PlanningMore_DesinscrireTout")
     *
     * @param mixed $user
     * @param mixed $evenement
     */
    public function desinscrireTout(DhtmlxEvenement $event)
    {
        $em = $this->getDoctrine()->getManager();
        $inscriptions = [];
        if (!is_null($event->getSerie())) {
            $inscriptions = $em->getRepository('UcaBundle:Inscription')->findBy(['creneau' => $event->getSerie()->getCreneau()->getId()]);
        } elseif (!is_null($event->getFormatSimple())) {
            $inscriptions = $em->getRepository('UcaBundle:Inscription')->findBy(['formatActivite' => $event->getFormatSimple()->getId()]);
        }

        foreach ($inscriptions as $inscription) {
            if (in_array($inscription->getStatut(), ['initialise', 'attentevalidationencadrant', 'attentevalidationgestionnaire', 'attenteajoutpanier', 'attentepaiement', 'valide'])) {
                $inscriptionService = $this->get('uca.inscription');
                $inscriptionService->setInscription($inscription);
                $inscriptionService->mailDesinscription($inscription);
                $inscription->seDesinscrire($this->getUser());
            }
        }

        $appels = $em->getRepository(Appel::class)->findBy(['dhtmlxEvenement' => $event->getId()]);
        foreach ($appels as $appel) {
            $em->remove($appel);
        }

        $em->flush();

        $this->addFlash('success', 'message.desinscription.success');

        return $this->redirectToRoute('UcaWeb_PlanningMore', ['id' => $event->getId()]);
    }

    /**
     * @Route("/ndef", name="Api_NDEFUser", methods={"POST"}, options={"expose"=true})
     */
    public function getNDEFUser(Request $request)
    {
        if ($request->get('id')) {
            $em = $this->getDoctrine()->getManager();
            //on reforme l'id comme il est stocké en base
            // ex : 04:25:56:9a:e7:5d:80 => 805DE79A562504
            $numeroNfc = strtoupper(implode('', array_reverse(explode(':', $request->get('id')))));
            $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['numeroNfc' => $numeroNfc]);
            if ($utilisateur) {
                return new JsonResponse(['user' => $utilisateur->getNom().' '.$utilisateur->getPrenom()]);
            }
        }

        return new JsonResponse(['user' => 'null']);
    }
}