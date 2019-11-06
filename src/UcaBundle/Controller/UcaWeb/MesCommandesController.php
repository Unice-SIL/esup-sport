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
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\Utilisateur;
use Spipu\Html2Pdf\Html2Pdf;
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
                return $this->redirectToRoute('UcaWeb_PaiementRecapitulatif', array('id' => $commande->getId(), 'typePaiement' => 'PAYBOX'));
            } else {
                $this->get('uca.flashbag')->addTranslatedFlashBag('danger', 'mentions.conditions.nonvalide');
            }
        }
        $twigConfig['form'] = $form->createView();
        $twigConfig['noAddButton'] = true;
        $twigConfig["codeListe"] = 'DetailsCommande';
        $twigConfig['retourBouton'] = true;
        $twigConfig["commande"] = $commande;
        $twigConfig["source"] = 'mescommandes';
        return $this->render('@Uca/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("Annuler/{id}",name="UcaWeb_MesCommandesAnnuler")
     */
    public function annulerAction(Request $request, Commande $commande)
    {
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
     * @Route("Export/{id}",name="UcaWeb_MesCommandesExport", options={"expose"=true})
     */
    public function exportAction(Request $request, Commande $commande)
    {
        $content = $this->renderView('@Uca/UcaWeb/Commande/Facture.html.twig', array('commande' => $commande));
        try {
            $pdf = new HTML2PDF("p", "A4", "fr");
            $pdf->pdf->SetAuthor('Université de Nice');
            $pdf->pdf->SetTitle('Facture');
            $pdf->writeHTML($content);
            $pdf->Output('Facture.pdf');
        } catch (HTML2PDF_exception $e) {
            die($e);
        }
    }
}
