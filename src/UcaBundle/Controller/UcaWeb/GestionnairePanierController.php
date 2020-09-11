<?php

/*
 * Classe - GestionnairePanierController
 *
 * Afficher le contenu du panier selon le statut
 * Suppression d'un article du panier
*/

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\DetailsGestionPanierDatatable;
use UcaBundle\Datatables\GestionPanierDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Form\NumeroChequeType;

/**
 * @Route("UcaWeb/CommandeEnAttente")
 * @Security("has_role('ROLE_ADMIN')")
 */
class GestionnairePanierController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_CommandeEnAttenteLister")
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function listerAction(Request $request)
    {
        $this->get('uca.timeout')->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(GestionPanierDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            $qb->andWhere('commande.statut LIKE :statutPanier');
            $qb->setParameter('statutPanier', 'apayer');

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'GestionCommande';

        return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaWeb_CommandeEnAttenteSupprimer")
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function supprimerAction(Request $request, Commande $commande)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$commande) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($commande, 'Supprimer');
        } else {
            $commande->changeStatut('annule', ['motifAnnulation' => 'refusgestionnaire', 'commentaireAnnulation' => null]);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($commande, 'Supprimer');
        }

        return $this->redirectToRoute('UcaWeb_CommandeEnAttenteLister');
    }

    /**
     * @Route("/{id}", name="UcaWeb_CommandeEnAttenteVoir", methods={"GET","HEAD","POST"})
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function voirAction(Request $request, Commande $commande)
    {
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(DetailsGestionPanierDatatable::class);
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
        $form = $this->get('form.factory')->create(NumeroChequeType::class);

        $twigConfig['formNumeroCheque'] = $form->createView();
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'gestioncaisse';

        return $this->render('@Uca/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/SupprimerArticle/{id}", name="UcaWeb_ArticleSupprimer")
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function supprimerArticleAction(Request $request, CommandeDetail $commandeDetail)
    {
        $em = $this->getDoctrine()->getManager();

        // $em->remove($commandeDetail);
        // $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($commandeDetail, 'Supprimer');

        return $this->redirectToRoute('UcaWeb_CommandeEnAttenteVoir', ['id' => $commandeDetail->getCommande()->getId()]);
    }
}
