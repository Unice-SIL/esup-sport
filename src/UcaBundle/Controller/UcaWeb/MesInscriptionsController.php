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
use UcaBundle\Entity\Inscription;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\Commande;

/**
 * @Route("UcaWeb/MesInscriptions")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesInscriptionsController extends Controller
{
    /**
     * @Route("/",name="UcaWeb_MesInscriptions")
     */
    public function listerAction(Request $request)
    {
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(MesInscriptionsDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->andWhere('utilisateur = :objectId');
            $qb->setParameter('objectId', $this->getUser()->getId());
            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'MesInscriptions';
        return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Annuler", name="UcaWeb_MesInscriptionsAnnuler")
     */
    public function annulerAction(Request $request, Inscription $item)
    {
        $em = $this->getDoctrine()->getManager();
        $item->setStatut('annule',  ['motifAnnulation' => 'annulationutilisateur']);
        $em->flush();
        return $this->redirectToRoute('UcaWeb_MesInscriptions');
    }

    /**
     * @Route("/{id}/AjoutPanier", name="UcaWeb_MesInscriptionsAjoutPanier")
     */
    public function ajoutPanierAction(Request $request, Inscription $inscription)
    {
        $em = $this->getDoctrine()->getManager();
        $inscriptionService = $this->get('uca.inscription');
        $inscriptionService->setInscription($inscription);
        $inscriptionService->ajoutPanier($inscription);
        $inscription->setStatut('attentepaiement');
        $em->flush();
        return $this->redirectToRoute('UcaWeb_Panier');
    }
}
