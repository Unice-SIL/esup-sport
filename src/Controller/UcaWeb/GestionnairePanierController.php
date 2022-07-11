<?php

/*
 * Classe - GestionnairePanierController
 *
 * Afficher le contenu du panier selon le statut
 * Suppression d'un article du panier
*/

namespace App\Controller\UcaWeb;

use App\Datatables\DetailsGestionPanierDatatable;
use App\Datatables\GestionPanierDatatable;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Form\NumeroChequeType;
use App\Service\Common\FlashBag;
use App\Service\Securite\TimeoutService;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaWeb/CommandeEnAttente")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class GestionnairePanierController extends AbstractController
{
    /**
     * @Route("/", name="UcaWeb_CommandeEnAttenteLister")
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, TimeoutService $timeoutService)
    {
        $timeoutService->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(GestionPanierDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            $qb->andWhere('commande.statut LIKE :statutPanier');
            $qb->setParameter('statutPanier', 'apayer');

            return $responseService->getResponse();
        }
        // Bouton Ajouter
        $usr = $this->getUser();
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'GestionCommande';

        return $this->render('UcaBundle/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaWeb_CommandeEnAttenteSupprimer")
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function supprimerAction(Request $request, Commande $commande, FlashBag $flashBag, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        if (!$commande) {
            $flashBag->addActionErrorFlashBag($commande, 'Supprimer');
        } else {
            $commande->changeStatut('annule', ['motifAnnulation' => 'refusgestionnaire', 'commentaireAnnulation' => null, 'em' => $em]);
            foreach ($commande->getCommandeDetails() as $commandeDetail) {
                if ($inscription = $commandeDetail->getInscription()) {
                    $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
                }
            }
            $em->flush();
            $flashBag->addActionFlashBag($commande, 'Supprimer');
        }

        return $this->redirectToRoute('UcaWeb_CommandeEnAttenteLister');
    }

    /**
     * @Route("/{id}", name="UcaWeb_CommandeEnAttenteVoir", methods={"GET","HEAD","POST"})
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function voirAction(Request $request, Commande $commande, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, EntityManagerInterface $em)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(DetailsGestionPanierDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commandedetail.commande = :commande');
            $qb->setParameter('commande', $commande);

            return $responseService->getResponse();
        }
        $form = $this->createForm(NumeroChequeType::class);

        $twigConfig['formNumeroCheque'] = $form->createView();
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'gestioncaisse';

        return $this->render('UcaBundle/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/SupprimerArticle/{id}", name="UcaWeb_ArticleSupprimer")
     * @Isgranted("ROLE_GESTION_PAIEMENT_COMMANDE")
     */
    public function supprimerArticleAction(Request $request, CommandeDetail $commandeDetail, FlashBag $flashBag, EntityManagerInterface $em)
    {
        // $em->remove($commandeDetail);
        // $em->flush();
        $flashBag->addActionFlashBag($commandeDetail, 'Supprimer');

        return $this->redirectToRoute('UcaWeb_CommandeEnAttenteVoir', ['id' => $commandeDetail->getCommande()->getId()]);
    }
}