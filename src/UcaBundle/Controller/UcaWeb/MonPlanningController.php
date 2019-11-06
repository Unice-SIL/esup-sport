<?php

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Appel;
use UcaBundle\Entity\DhtmlxEvenement;
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
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getInscriptionsValidee();
                $eventName = $dhtmlxEvenement->getSerie()->getCreneau()->getFormatActivite()->getActivite()->getLibelle();
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getInscriptionsValidee();
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
        $form2 = $this->get('form.factory')->create(PlanningMailType::class, null, ['liste_destinataires' => $destinataires]);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($dhtmlxEvenement);
            $em->flush();
        }

        $twigConfig['evenement'] = $dhtmlxEvenement;
        $twigConfig['eventName'] = $eventName;
        $twigConfig['isEncadrant'] = $this->getUser()->isEncadrantEvenement($dhtmlxEvenement);
        $twigConfig['inscriptions'] = $inscriptions;
        $twigConfig['form'] = $form->createView();
        $twigConfig['form2'] = $form2->createView();

        return $this->render('@Uca/UcaWeb/Utilisateur/More.html.twig', $twigConfig);
    }

    /**
     * @Route("/mail/{id}",name="UcaWeb_PlanningMore_mail")
     */
    public function sendMail(Request $request, DhtmlxEvenement $dhtmlxEvenement)
    {
        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getInscriptionsValidee();
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getInscriptionsValidee();
        }

        $destinataires = [];
        foreach ($inscriptions as $key => $inscription) {
            $user = $inscription->getUtilisateur();
            $key = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
            $destinataires[$key] = $user->getEmail();
        }
        $form2 = $this->get('form.factory')->create(PlanningMailType::class, null, ['liste_destinataires' => $destinataires]);
        if ($request->isMethod('POST') && $form2->handleRequest($request)->isValid()) {
            $message = $form2->getData()['mail'];
            $objet = $form2->getData()['objet'];
            $setTo = $form2->getData()['destinataires'];
            $mailer = $this->container->get('mailService');
            $copie =  $this->getUser()->getEmail();
            $mailer->sendMailWithTemplate(
                $objet,
                $setTo,
                '@Uca/Email/Calendrier/MailPourTousLesInscripts.html.twig',
                ['message' => $message],
                $copie
            );
        }

        return new JsonResponse(['statut' => 1]);
    }
}
