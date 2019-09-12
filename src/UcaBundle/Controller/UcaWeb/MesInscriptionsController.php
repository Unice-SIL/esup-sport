<?php

namespace UcaBundle\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Datatables\MesInscriptionsDatatable;
use UcaBundle\Datatables\GestionInscriptionDatatable;
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\Commande;


/**
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesInscriptionsController extends Controller
{
    /**
     * @Route("UcaGest/GestionInscription",name="UcaGest_GestionInscription")
     * @Route("UcaWeb/MesInscriptions",name="UcaWeb_MesInscriptions")
     */
    public function listerAction(Request $request)
    {
        if($request->get('_route') == 'UcaGest_GestionInscription' && !$this->isGranted('ROLE_GESTION_INSCRIPTION')){
            return $this->redirectToRoute('UcaWeb_MesInscriptions');
        }
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        if($request->get('_route') == 'UcaGest_GestionInscription'){
            $datatable = $this->get('sg_datatables.factory')->create(GestionInscriptionDatatable::class);
            $twigConfig['codeListe'] = 'GestionInscription';
        }else{
            $datatable = $this->get('sg_datatables.factory')->create(MesInscriptionsDatatable::class);
            $twigConfig['codeListe'] = 'MesInscriptions';
        }
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            if($request->get('_route') == 'UcaWeb_MesInscriptions'){
                $qb->andWhere('utilisateur = :objectId');
                $qb->setParameter('objectId', $this->getUser()->getId());
            }
            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("UcaWeb/{id}/Annuler", name="UcaWeb_MesInscriptionsAnnuler")
     */
    public function annulerAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        if($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))){
            $inscription->setStatut('annule',  ['motifAnnulation' => 'annulationutilisateur']);
            $redirect = $this->redirectToRoute('UcaWeb_MesInscriptions');
        }else if($this->isGranted('ROLE_GESTION_INSCRIPTION')){
            $inscription->setStatut('annule',  ['motifAnnulation' => 'annulationgestionnaire']);
            $redirect = $this->redirectToRoute('UcaGest_GestionInscription');
        }
        $em->flush();
        return $redirect;
    }

    /**
     * @Route("UcaWeb/{id}/AjoutPanier", name="UcaWeb_MesInscriptionsAjoutPanier")
     */
    public function ajoutPanierAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->ajoutPanier($inscription);
        $inscription->setStatut('attentepaiement');
        $em->flush();
        if($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))){
            return $this->redirectToRoute('UcaWeb_Panier');
        }else if($this->isGranted('ROLE_GESTION_INSCRIPTION')){
            return $this->redirectToRoute('UcaGest_GestionInscription');
        }
    }

    /**
     * @Route("UcaWeb/{id}/SeDesinscrire", name="UcaWeb_MesInscriptionsSeDesinscrire")
     */
    public function seDesinscrireAction(Request $request, Inscription $inscription)
    {
        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->mailDesinscription($inscription);

        $em = $this->getDoctrine()->getManager();
        $inscription->setStatut('desinscrit');
        $inscription->setDateDesinscription(new \DateTime());
        $em->flush();
        if($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))){
            return $this->redirectToRoute('UcaWeb_MesInscriptions');
        }else if($this->isGranted('ROLE_GESTION_INSCRIPTION')){
            return $this->redirectToRoute('UcaGest_GestionInscription');
        }
    }

     /**
     * @Route("UcaWeb/MesInscriptions/{id}",name="UcaWeb_MesInscriptionsVoir")
     * @Route("UcaGest/GestionInscription/{id}",name="UcaGest_GestionInscriptionVoir")
     */
    public function voirAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $twigConfig['noAddButton'] = true;
        $twigConfig["codeListe"] = 'Inscription';
        $twigConfig['retourBouton'] = true;
        $twigConfig["inscription"] = $inscription;
        if($inscription->getUtilisateur() == $this->getUser() && !($request->getScheme().'://'.$request->getHttpHost().$this->generateUrl('UcaGest_GestionInscription') == $request->headers->get('referer'))){
            $twigConfig["source"] = 'mesinscriptions';
        }else if($this->isGranted('ROLE_GESTION_INSCRIPTION')){
            $twigConfig["source"] = 'gestioninscription';
        }
        return $this->render('@Uca/UcaWeb/Inscription/DetailInscription.html.twig', $twigConfig);
    }

}
