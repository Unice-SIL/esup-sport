<?php
/*
 * Classe - MesCreditsController
 *
 * Gestion des crédits utilisateur (côté web)
*/

namespace UcaBundle\Controller\UcaWeb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\MesCreditsDatatable;
use UcaBundle\Entity\UtilisateurCreditHistorique;

/**
 * @Route("UcaWeb/MesCredits")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class MesCreditsController extends Controller
{
    /**
     * @Route("/", name="UcaWeb_MesCredits", methods={"GET"})
     */
    public function voirCreditsAction(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(MesCreditsDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
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
        $twigConfig['extraDisplay'] = '@Uca/UcaWeb/Utilisateur/MesCredits.html.twig';

        return $this->render('@Uca/Common/Liste/Datatable_UcaWeb.html.twig', $twigConfig);
    }

    /** @Route("/Export/{id}",name="UcaWeb_MesCreditsExport", options={"expose"=true}) */
    public function exportCreditAction(Request $request, UtilisateurCreditHistorique $credit)
    {
        if ('Ajout manuel de crédit' != $credit->getOperation() && $credit->getUtilisateur() != $this->getUser() && !$this->get('security.authorization_checker')->isGranted(' ROLE_GESTION_CREDIT_UTILISATEUR_LECTURE')) {
            return $this->redirectToRoute('UcaWeb_MesCredits');
        }
        $twigConfig['credit'] = $credit;
        $pdf = $this->container->get('uca.creationpdf');
        $pdf->createView('@Uca/UcaWeb/Utilisateur/Credit.html.twig', $twigConfig);
        $pdf->createPdf(['author' => 'Université de Nice', 'title' => 'Credit', 'output' => 'Credit.pdf']);
    }
}
