<?php
/*
 * Classe - HistoriqueCreditController
 *
 * Gestion de l'historique des crédits utilisateur
*/

namespace UcaBundle\Controller\UcaGest\Gestion;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\UtilisateurCreditHistoriqueDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\UtilisateurCreditHistorique;
use UcaBundle\Form\GestionCommandeType;

/**
 * @Route("UcaGest/Reporting/Credits")
 * @Isgranted("ROLE_GESTION_CREDIT_UTILISATEUR_LECTURE")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class HistoriqueCreditController extends Controller
{
    /** @Route("/" , name="UcaGest_ReportingCredit") */
    public function listerCredit(Request $request)
    {
        $datatable = $this->get('sg_datatables.factory')->create(UtilisateurCreditHistoriqueDatatable::class);
        $datatable->buildDatatable();
        $form = $this->get('form.factory')->create(GestionCommandeType::class);
        $twigConfig['datatable'] = $datatable;
        $twigConfig['codeListe'] = 'UtilisateurCreditHistorique';
        $twigConfig['form'] = $form->createView();

        if ($request->isXmlHttpRequest()) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }
        // L'ajout se fera au niveau de l'utilisateur
        $twigConfig['noAddButton'] = true;
        $twigConfig['gestionButtons'] = true;
        $twigConfig['exportAll'] = true;

        return $this->render('@Uca/UcaGest/Reporting/UtilisateurCreditHistorique/Datatable.html.twig', $twigConfig);
    }

    /** @Route("/Extraction/{dateDebut}/{dateFin}" , name="UcaGest_ReportingCreditExtraction", options={"expose"=true})*/
    public function creditsExtractionAction(Request $request, $dateDebut, $dateFin)
    {
        $em = $this->getDoctrine()->getManager();
        $dateDebut = 'null' !== $dateDebut ? \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : null;
        $dateFin = 'null' !== $dateFin ? \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : null;
        $listeCredit = $em->getRepository(UtilisateurCreditHistorique::class)->findExtractedCredits($dateDebut, $dateFin);
        if (!empty($listeCredit)) {
            $translator = $this->get('translator');
            $extractionService = $this->container->get('uca.extraction.excel');

            // En-têtes tableau
            $dataHeader = ['Historique des crédits' => [
                0 => $translator->trans('common.date'),
                1 => $translator->trans('commande.avoir.libelle'),
                2 => $translator->trans('commande.libelle'),
                3 => $translator->trans('utilisateur.nom'),
                4 => $translator->trans('utilisateur.prenom'),
                5 => $translator->trans('utilsateur.credit.operation'),
                6 => $translator->trans('common.statut'),
                7 => $translator->trans('utilsateur.credit.typeoperation'),
                8 => $translator->trans('common.total'),
            ]];

            // Données tableau
            $datas = [];
            foreach ($listeCredit as $credit) {
                $datas[] = [
                    0 => $credit->getDate(),
                    1 => $credit->getAvoir(),
                    2 => $credit->getCommandeAssociee(),
                    3 => $credit->getUtilisateur()->getNom(),
                    4 => $credit->getUtilisateur()->getPrenom(),
                    5 => $credit->getOperation(),
                    6 => $credit->getStatut(),
                    7 => $credit->getTypeOperation(),
                    8 => $credit->getMontant(),
                ];
            }

            $extractionService->setWorksheets($dataHeader, $dateDebut, $dateFin);
            foreach ($extractionService->getSpreadsheet()->getAllSheets() as $worksheet) {
                $extractionService->setWorksheet($worksheet, $datas, 'I');
            }

            $writer = $extractionService->getWriter();

            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );

            $filename = 'extract_crédit_utilisateurs_'.date('Y-m-d').'_'.date('H-i-s').'.xlsx';
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

            return $response;
        }

        return $this->redirectToRoute('UcaGest_ReportingCredit');
    }

    /** @Route("/ExportAll/{date}/{recherche}", name="UcaWeb_MesCreditsExportAll", options={"expose"=true})*/
    public function exportAllAction(Request $request, $date = null, $recherche = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(UtilisateurCreditHistorique::class);

        'null' !== $date ?: $date = null;
        'null' !== $recherche ?: $recherche = null;
        $credits = $repo->findAllCreditsByOperation($date, $recherche, 'Ajout manuel de crédit');
        $avoirs = $repo->findAllCreditsByOperation($date, $recherche, ["Génération d'avoir", "Report d'avoir"]);

        if (!empty($avoirs) || !empyt($credits)) {
            $pdf = $this->container->get('uca.creationpdf');
            foreach ($credits as $credit) {
                $pdf->createMultipleView('@Uca/UcaWeb/Utilisateur/Credit.html.twig', ['credit' => $credit]);
            }
            foreach ($avoirs as $avoir) {
                $twigConfig = [
                    'commande' => $em->getReference(Commande::class, $avoir->getCommandeAssociee()),
                    'refAvoir' => $avoir->getAvoir(),
                ];
                if ("Report d'avoir" == $avoir->getOperation()) {
                    $twigConfig['reportAvoir'] = true;
                }
                $pdf->createMultipleView('@Uca/UcaWeb/Commande/Avoir.html.twig', $twigConfig);
            }
            $pdf->createMultiplePdf(['author' => 'Université de Nice', 'title' => 'Credit&Avoir', 'output' => 'Credit&Avoir.pdf']);
        } else {
            $this->get('uca.flashbag')->addMessageFlashBag('common.aucune.facture', 'danger');

            return $this->redirectToRoute('UcaGest_ReportingCredit');
        }
    }
}
