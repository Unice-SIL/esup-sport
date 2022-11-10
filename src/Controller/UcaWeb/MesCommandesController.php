<?php

/*
 * Classe - MesCommandesController
 *
 * Gestion de l'écran de mes commandes des utilisateurs
 * Consulter une commande / un Avoir
 * Export les commandes / avoirs au format pdf
*/

namespace App\Controller\UcaWeb;

use App\Entity\Uca\Commande;
use Spipu\Html2Pdf\Html2Pdf;
use App\Entity\Uca\Parametrage;
use App\Service\Common\FlashBag;
use App\Form\ValiderPaiementPayboxType;
use App\Service\Securite\TimeoutService;
use Doctrine\ORM\EntityManagerInterface;
use App\Datatables\MesCommandesDatatable;
use App\Datatables\DetailsCommandeDatatable;
use App\Repository\ParametrageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaWeb/MesCommandes")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesCommandesController extends AbstractController
{
    /**
     * @Route("/",name="UcaWeb_MesCommandes")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, TimeoutService $timeoutService)
    {
        $timeoutService->nettoyageCommandeEtInscription();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(MesCommandesDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
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

        return $this->render('UcaBundle/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}",name="UcaWeb_MesCommandesVoir")
     */
    public function voirAction(Request $request, Commande $commande, FlashBag $flashBag, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, EntityManagerInterface $em)
    {
        if ($commande->getUtilisateur() != $this->getUser()) {
            return $this->redirectToRoute('UcaWeb_MesCommandes');
        }
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(DetailsCommandeDatatable::class);
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
        $form = $this->createForm(ValiderPaiementPayboxType::class, $commande);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if ($commande->getCgvAcceptees()) {
                $em->persist($commande);
                $em->flush();

                return $this->redirectToRoute('UcaWeb_PaiementRecapitulatif', ['id' => $commande->getId(), 'typePaiement' => 'PAYBOX']);
            }
            $flashBag->addTranslatedFlashBag('danger', 'mentions.conditions.nonvalide');
        }
        $twigConfig['form'] = $form->createView();
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'DetailsCommande';
        $twigConfig['retourBouton'] = true;
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'mescommandes';

        return $this->render('UcaBundle/UcaWeb/Commande/DetailCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/Annuler/{id}",name="UcaWeb_MesCommandesAnnuler")
     */
    public function annulerAction(Request $request, Commande $commande, FlashBag $flashBag, EntityManagerInterface $em)
    {
        if ($commande->getUtilisateur() != $this->getUser()) {
            return $this->redirectToRoute('UcaWeb_MesCommandes');
        }
        if (!$commande) {
            $flashBag->addActionErrorFlashBag($commande, 'Supprimer');
        } else {
            $commande->changeStatut('annule', ['motifAnnulation' => 'annulationutilisateur', 'commentaireAnnulation' => null, 'em' => $em]);
            $em->flush();
            $flashBag->addActionFlashBag($commande, 'Supprimer');
        }

        return $this->redirectToRoute('UcaWeb_MesCommandes');
    }

    /**
     * @Route("/Export/{id}",name="UcaWeb_MesCommandesExport", options={"expose"=true})
     */
    public function exportAction(Request $request, Commande $commande, ParametrageRepository $paramRepo)
    {
        if ($commande->getUtilisateur() != $this->getUser() && !$this->isGranted('ROLE_GESTION_COMMANDES')) {
            return $this->redirectToRoute('UcaWeb_MesCommandes');
        }

        $parametrage = $paramRepo->findOneById(1);
        $content = $this->renderView('UcaBundle/UcaWeb/Commande/Facture.html.twig', ['commande' => $commande, 'parametrage' => $parametrage]);

        try {
            $pdf = new HTML2PDF('p', 'A4', 'fr');
            $pdf->setTestIsImage(false);
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
    public function exportAvoirAction(Request $request, Commande $commande, $refAvoir, ParametrageRepository $paramRepo)
    {
        if ($commande->getUtilisateur() != $this->getUser() && !$this->isGranted('ROLE_GESTION_COMMANDES')) {
            return $this->redirectToRoute('UcaWeb_MesCredits');
        }

        $parametrage = $paramRepo->findOneById(1);
        $content = $this->renderView('UcaBundle/UcaWeb/Commande/Avoir.html.twig', ['commande' => $commande, 'refAvoir' => $refAvoir,  'parametrage' => $parametrage]);

        try {
            $pdf = new HTML2PDF('p', 'A4', 'fr');
            $pdf->setTestIsImage(false);
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
