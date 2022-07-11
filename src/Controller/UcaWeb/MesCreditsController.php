<?php
/*
 * Classe - MesCreditsController
 *
 * Gestion des crédits utilisateur (côté web)
*/

namespace App\Controller\UcaWeb;

use App\Datatables\MesCreditsDatatable;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Uca\UtilisateurCreditHistorique;
use App\Service\Common\CreationPdf;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaWeb/MesCredits")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesCreditsController extends AbstractController
{
    /**
     * @Route("/", name="UcaWeb_MesCredits", methods={"GET"})
     */
    public function voirCreditsAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(MesCreditsDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb()
                ->andWhere('utilisateurcredithistorique.utilisateur = :usr')
                ->andWhere('utilisateurcredithistorique.statut <> :statut')
                ->setParameter('usr', $this->getUser())
                ->setParameter('statut', 'annule')
            ;

            return $responseService->getResponse();
        }
        // Bouton Ajouter

        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'MesCredits';
        $twigConfig['extraDisplay'] = 'UcaBundle/UcaWeb/Utilisateur/MesCredits.html.twig';

        return $this->render('UcaBundle/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /** @Route("/Export/{id}",name="UcaWeb_MesCreditsExport", options={"expose"=true}) */
    public function exportCreditAction(Request $request, UtilisateurCreditHistorique $credit, CreationPdf $pdf)
    {
        if ('Ajout manuel de crédit' != $credit->getOperation() && $credit->getUtilisateur() != $this->getUser() && !$this->isGranted(' ROLE_GESTION_CREDIT_UTILISATEUR_LECTURE')) {
            return $this->redirectToRoute('UcaWeb_MesCredits');
        }
        $twigConfig['credit'] = $credit;
        $pdf->createView('UcaBundle/UcaWeb/Utilisateur/Credit.html.twig', $twigConfig);
        return $pdf->createPdf(['author' => 'Université de Nice', 'title' => 'Credit', 'output' => 'Credit.pdf']);
    }
}
