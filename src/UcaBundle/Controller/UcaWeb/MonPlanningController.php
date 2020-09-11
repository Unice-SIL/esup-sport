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
        foreach ($inscriptions as $key => $inscription) {
            $user = $inscription->getUtilisateur();
            $key = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
            $destinataires[$key] = $user->getEmail();
        }

        $formMail = $this->get('form.factory')->create(PlanningMailType::class, null, ['liste_destinataires' => $destinataires]);
        $formMail->handleRequest($request);
        $validation = $formMail->isValid();
        if ($request->isMethod('POST') && $validation) {
            $message = $formMail->getData()['mail'];
            $objet = $formMail->getData()['objet'];
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
                $pdf->pdf->SetTitle('Facture');
                $pdf->writeHTML($content);
                $pdf->Output('Facture.pdf');
            } catch (HTML2PDF_exception $e) {
                die($e);
            }
        } else {
            return $this->redirectToRoute('UcaWeb_MonPlanning');
        }
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
                $this->forward('UcaBundle\Controller\UcaWeb\MesInscriptionsController::seDesinscrireAction', ['request' => $request, 'inscription' => $participant]);
            }
        }

        return $this->redirectToRoute('UcaWeb_PlanningMore', ['id' => $evenement]);
    }
}
