<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\MesCommandesDatatable;
use UcaBundle\Datatables\DetailsCommandeDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\Appel;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Form\AppelUtilisateurType;
use UcaBundle\Form\EvenementType;
use UcaBundle\Form\PlanningMailType;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $twigConfig['type'] = "encadrant";
        if ($isEncadrant) {
            $twigConfig['role'] = "encadrant";
        } else {
            $twigConfig['role'] = "user";
        }
        return $this->render('@Uca/UcaWeb/Utilisateur/MonPlanning.html.twig', $twigConfig);
    }

    /**
     * @Route("/more/{id}",name="UcaWeb_PlanningMore")
     * @Route("/more/",name="UcaWeb_PlanningMore_NoId")
    */
    public function voirAction(Request $request, DhtmlxEvenement $dhtmlxEvenement)
    {
        $twigConfig = array();
        $inscriptions = array();
        $em = $this->getDoctrine()->getManager();        
        $eventName = "";
        if($dhtmlxEvenement->getSerie() != null){
            if($dhtmlxEvenement->getSerie()->getCreneau() != null){
                $inscriptions =  $dhtmlxEvenement->getSerie()->getCreneau()->getInscriptionsValidee();
                $eventName = $dhtmlxEvenement->getSerie()->getCreneau()->getFormatActivite()->getActivite()->getLibelle();
            }
        }
        if($dhtmlxEvenement->getFormatSimple()){
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getInscriptionsValidee();
            $eventName = $dhtmlxEvenement->getFormatSimple()->getActivite()->getLibelle();
        }
        


        $existingAppel = $em->getRepository(Utilisateur::Class)->findUtilisateurByEvenement($dhtmlxEvenement->getId());
        foreach($inscriptions as $key => $inscription){
            if(!in_array($inscription->getUtilisateur(), $existingAppel)){
                $appel = new Appel();
                $appel->setUtilisateur($inscription->getUtilisateur());
                $appel->setDhtmlxEvenement($dhtmlxEvenement);
                $dhtmlxEvenement->addAppel($appel);
            }
        }

        $form = $this->get('form.factory')->create(EvenementType::class, $dhtmlxEvenement);
        $form2 = $this->get('form.factory')->create(PlanningMailType::class);


        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($dhtmlxEvenement);
            $em->flush();
        }
        
        $twigConfig["evenement"] = $dhtmlxEvenement;
        $twigConfig["eventName"] = $eventName;
        $twigConfig["isEncadrant"] = $this->getUser()->isEncadrantEvenement($dhtmlxEvenement);
        $twigConfig["inscriptions"] = $inscriptions;
        $twigConfig["form"] = $form->createView();
        $twigConfig["form2"] = $form2->createView();
        return $this->render('@Uca/UcaWeb/Utilisateur/More.html.twig', $twigConfig);
    }

    /**
     * @Route("/mail/{id}",name="UcaWeb_PlanningMore_mail")
    */
    public function sendMail(Request $request, DhtmlxEvenement $dhtmlxEvenement){
        if($dhtmlxEvenement->getSerie() != null){
            if($dhtmlxEvenement->getSerie()->getCreneau() != null){
                $inscriptions =  $dhtmlxEvenement->getSerie()->getCreneau()->getInscriptionsValidee();
            }
        }
        if($dhtmlxEvenement->getFormatSimple()){
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getInscriptionsValidee();
        }

        $form2 = $this->get('form.factory')->create(PlanningMailType::class);
        if ($request->isMethod('POST') && $form2->handleRequest($request)->isValid()) {
            $message = $form2->getData()["mail"];
            $mailer = $this->container->get('mailService');
            $setTo = array();
             foreach($inscriptions as $key => $inscription){
                $user = $inscription->getUtilisateur();
                $setTo[$user->getEmail()] = ucfirst($user->getPrenom()) . ' ' . ucfirst($user->getNom());
             }
            $mailer->sendMailWithTemplate(
                'demande.validation',
                $setTo,
                '@Uca/Email/Calendrier/MailPourTousLesInscripts.html.twig',
                ['message' => $message]
            );
        }

        return new JsonResponse(array("statut" => 1));
    }
}