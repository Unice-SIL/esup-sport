<?php

namespace UcaBundle\Controller\UcaGest\Gestion;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\DetailsCommandeDatatable;
use UcaBundle\Datatables\GestionCommandesDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Form\GestionCommandeType;

/**
 * @Route("UcaGest/ReportingCommandes")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class ReportingController extends Controller
{
    /**
     * @Route("/",name="UcaGest_ReportingCommandes")
     */
    public function listerAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(GestionCommandesDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        $form = $this->get('form.factory')->create(GestionCommandeType::class);
        $twigConfig['form'] = $form->createView();
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commande.statut NOT LIKE :panier');
            $qb->setParameter('panier', 'panier');

            return $responseService->getResponse();
        }

        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'ReportingCommandes';

        if ($this->isGranted('ROLE_GESTION_PAIEMENT_COMMANDE') or $this->isGranted('ROLE_GESTION_COMMANDES')) {
            //Ajout du bouton exporter toutes les factures
            $twigConfig['exportAll'] = true;
            //Ajout du bouton extraire les commandes
            $twigConfig['gestionButtons'] = true;
        }

        return $this->render('@Uca/UcaGest/Reporting/Commandes/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}",name="UcaGest_ReportingCommandeDetails")
     */
    public function voirAction(Request $request, Commande $commande)
    {
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(DetailsCommandeDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commandedetail.commande = :commande');
            $qb->setParameter('commande', $commande);

            return $responseService->getResponse();
        }
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'DetailsCommande';
        $twigConfig['retourBouton'] = true;
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'reporting';

        return $this->render('@Uca/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
    }
}
