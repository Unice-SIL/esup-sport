<?php

/*
 * Classe - MesCommandesController
 *
 * Gestion de l'écran de mes commandes des utilisateurs
 * Consulter une commande / un Avoir
 * Export les commandes / avoirs au format pdf
*/

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\DetailsCommandeDatatable;
use UcaBundle\Datatables\MesCommandesDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\Parametrage;
use UcaBundle\Form\ValiderPaiementPayboxType;

/**
 * @Route("UcaWeb/MesCommandes")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesCommandesController extends Controller
{
    /**
     * @Route("/",name="UcaWeb_MesCommandes")
     */
    public function listerAction(Request $request)
    {
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(MesCommandesDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commande.statut NOT LIKE :panier');
            $qb->andWhere('commande.utilisateur = :user');
            $qb->setParameter('panier', 'panier');
            $qb->setParameter('user', $this->getUser());

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'MesCommandes';

        return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}",name="UcaWeb_MesCommandesVoir")
     */
    public function voirAction(Request $request, Commande $commande)
    {
        if ($commande->getUtilisateur() != $this->getUser()) {
            return $this->redirectToRoute('UcaWeb_MesCommandes');
        }
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
        $form = $this->get('form.factory')->create(ValiderPaiementPayboxType::class, $commande);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if ($commande->getCgvAcceptees()) {
                $em->persist($commande);
                $em->flush();

                return $this->redirectToRoute('UcaWeb_PaiementRecapitulatif', ['id' => $commande->getId(), 'typePaiement' => 'PAYBOX']);
            }
            $this->get('uca.flashbag')->addTranslatedFlashBag('danger', 'mentions.conditions.nonvalide');
        }
        $twigConfig['form'] = $form->createView();
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'DetailsCommande';
        $twigConfig['retourBouton'] = true;
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'mescommandes';

        return $this->render('@Uca/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/Annuler/{id}",name="UcaWeb_MesCommandesAnnuler")
     */
    public function annulerAction(Request $request, Commande $commande)
    {
        if ($commande->getUtilisateur() != $this->getUser()) {
            return $this->redirectToRoute('UcaWeb_MesCommandes');
        }
        $em = $this->getDoctrine()->getManager();
        if (!$commande) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($commande, 'Supprimer');
        } else {
            $commande->changeStatut('annule', ['motifAnnulation' => 'annulationutilisateur', 'commentaireAnnulation' => null]);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($commande, 'Supprimer');
        }

        return $this->redirectToRoute('UcaWeb_MesCommandes');
    }

    /**
     * @Route("/Export/{id}",name="UcaWeb_MesCommandesExport", options={"expose"=true})
     */
    public function exportAction(Request $request, Commande $commande)
    {
        if ($commande->getUtilisateur() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_GESTION_COMMANDES')) {
            return $this->redirectToRoute('UcaWeb_MesCommandes');
        }

        $parametrage = $this->getDoctrine()->getRepository(Parametrage::class)->findOneById(1);
        $content = $this->renderView('@Uca/UcaWeb/Commande/Facture.html.twig', ['commande' => $commande, 'parametrage' => $parametrage]);

        try {
            $pdf = new HTML2PDF('p', 'A4', 'fr');
            $pdf->pdf->SetAuthor('Université Côte d\'Azur');
            $pdf->pdf->SetTitle('Facture');
            $pdf->writeHTML($content);
            $pdf->Output('Facture.pdf');
        } catch (HTML2PDF_exception $e) {
            die($e);
        }

        return new Response();
    }

    /**
     * @Route("/Export/{id}/Avoir/{refAvoir}",name="UcaWeb_MesAvoirsExport", options={"expose"=true})
     *
     * @param mixed $refAvoir
     */
    public function exportAvoirAction(Request $request, Commande $commande, $refAvoir)
    {
        if ($commande->getUtilisateur() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted('ROLE_GESTION_COMMANDES')) {
            return $this->redirectToRoute('UcaWeb_MesCredits');
        }

        $parametrage = $this->getDoctrine()->getRepository(Parametrage::class)->findOneById(1);
        $content = $this->renderView('@Uca/UcaWeb/Commande/Avoir.html.twig', ['commande' => $commande, 'refAvoir' => $refAvoir,  'parametrage' => $parametrage]);

        try {
            $pdf = new HTML2PDF('p', 'A4', 'fr');
            $pdf->pdf->SetAuthor('Université Côte d\'Azur');
            $pdf->pdf->SetTitle('Avoir');
            $pdf->writeHTML($content);
            $pdf->Output('Avoir.pdf');
        } catch (HTML2PDF_exception $e) {
            die($e);
        }

        return new Response();
    }
}
