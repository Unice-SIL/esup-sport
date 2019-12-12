<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\InscriptionAValiderDatatable;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\Autorisation;
use UcaBundle\Entity\Creneau;


/**
 * @Route("UcaWeb/ValidationInscription")
 * @Security("has_role('ROLE_ENCADRANT') or has_role('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
 */
class ValidationInscriptionEncadrantController extends Controller
{
    /**
     * @Route("/",name="UcaWeb_InscriptionAValiderLister")
    */
    public function listerAction(Request $request)
    {
        if(($request->get('type') == 'gestionnaire' && !$this->isGranted('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')) || $request->get('type') == 'encadrant' && !$this->isGranted('ROLE_ENCADRANT')){
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(InscriptionAValiderDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            if($request->get('type') == 'gestionnaire'){
                $qb->where('inscription.statut LIKE :statutInscription');
                $qb->setParameter('statutInscription', "attentevalidationgestionnaire");
            }else{
                $qb->innerJoin('UcaBundle\Entity\Utilisateur','u','WITH','u.id = :userId');
                $qb->andWhere('inscription.id IN (:inscriptionsAValider)');
                $qb->andWhere('inscription.statut LIKE :statutInscription');
                $qb->setParameter('inscriptionsAValider', $this->getUser()->getInscriptionsAValider());
                $qb->setParameter('userId', $this->getUser()->getId());
                $qb->setParameter('statutInscription', "attentevalidationencadrant");
            }
            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'InscriptionAValider';
        return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}",name="UcaWeb_InscriptionAValiderVoir")
     * @Security("has_role('ROLE_ENCADRANT') or has_role('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
    */
    public function voirAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
       
        $twigConfig['item'] = $inscription;
        return $this->render('@Uca/UcaWeb/Inscription/ValidationInscriptionEncadrant.html.twig', $twigConfig);
    }

    /**
     * @Route("/ValiderIncriptionParEncadrant/{id}",name="UcaWeb_InscriptionValideeParEncadrant")
     * @Security("has_role('ROLE_ENCADRANT')")
    */
    public function validationParEncadrantAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();

        foreach($inscription->getAutorisations()->getIterator() as $autorisation){
            $autorisation->setValideParEncadrant(true);
            $inscription->updateStatut();
        }

        foreach($inscription->getEncadrants()->getIterator() as $encadrant){
            $encadrant->getInscriptionsAValider()->removeElement($inscription);
            $inscription->removeEncadrant($encadrant);
        }
        $em->flush();

        $this->get('uca.flashbag')->addMessageFlashBag('inscription.confirmation.valider', 'success');

        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'encadrant']);
    }

     /**
     * @Route("/RefuserIncriptionParEncadrant/{id}",name="UcaWeb_InscriptionRefuseeParEncadrant")
     * @Security("has_role('ROLE_ENCADRANT')")
    */
    public function refusParEncadrantAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) $motif = $request->request->get('motifRefus');
        else $motif = '';
        $inscription->setStatut('annule',  
        ['motifAnnulation' => 'inscription.refus.encadrant', 
        'commentaireAnnulation' => $motif]);

        foreach($inscription->getAutorisations()->getIterator() as $autorisation){
            $autorisation->setValideParEncadrant(false);
            $autorisation->updateStatut();
        }
        foreach($inscription->getEncadrants()->getIterator() as $encadrant){
            $encadrant->getInscriptionsAValider()->removeElement($inscription);
            $inscription->removeEncadrant($encadrant);
        }
        
        $em->flush();
        $this->get('uca.flashbag')->addMessageFlashBag('inscription.confirmation.refuser', 'success');

        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'encadrant']);
    }
    
    /**
     * @Route("/telechargerJustificatif/{id}",name="UcaWeb_TelechargerJustificatif", options={"expose"=true})
     * @Security("has_role('ROLE_ENCADRANT') or has_role('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
    */
    public function telechargerJustificatifAction(Request $request, Autorisation $autorisation)
    {
        $em = $this->getDoctrine()->getManager();
        $file = $this->get('kernel')->getProjectDir().'/web/upload/private/fichiers/'.$autorisation->getJustificatif();
        $response = new Response();
        return $response->setContent(file_get_contents($file));
       
      
    }

    /**
     * @Route("/ValiderIncriptionParGestionnaire/{id}",name="UcaWeb_InscriptionValideeParGestionnaire")
     * @Security("has_role('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
    */
    public function validationParGestionnaireAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();

        foreach($inscription->getAutorisations()->getIterator() as $autorisation){
            $autorisation->setValideParGestionnaire(true);
            $inscription->updateStatut();
        }

        $em->flush();

        $this->get('uca.flashbag')->addMessageFlashBag('inscription.confirmation.valider', 'success');

        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();
        
        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'gestionnaire']);
    }

    /**
     * @Route("/RefuserIncriptionParGestionnaire/{id}",name="UcaWeb_InscriptionRefuseeParGestionnaire")
     * @Security("has_role('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION')")
    */
    public function refusParGestionnaireAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isMethod('POST')) $motif = $request->request->get('motifRefus');
        else $motif = '';
        $inscription->setStatut('annule',  [
            'motifAnnulation' => 'inscription.refus.gestionnaire', 
            'commentaireAnnulation' => $motif]
        );

        foreach($inscription->getAutorisations()->getIterator() as $autorisation){
            $autorisation->setValideParGestionnaire(false);
            $autorisation->updateStatut();
        }

        $em->flush();

        $this->get('uca.flashbag')->addMessageFlashBag('inscription.confirmation.refuser', 'success');

        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->envoyerMailInscriptionNecessitantValidation();

        return $this->redirectToRoute('UcaWeb_InscriptionAValiderLister', ['type' => 'gestionnaire']);
    }
}