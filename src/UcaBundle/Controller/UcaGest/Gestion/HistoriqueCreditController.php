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
use UcaBundle\Form\FiltreExtractionType;

/**
 * @Route("UcaGest/Reporting/Credits")
 * @Isgranted("ROLE_GESTION_CREDIT_UTILISATEUR_LECTURE")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class HistoriqueCreditController extends Controller
{
    /** @Route("/" , name="UcaGest_ReportingCredit")*/
    public function listerCredit(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(UtilisateurCreditHistorique::class);
        $datatable = $this->get('sg_datatables.factory')->create(UtilisateurCreditHistoriqueDatatable::class);
        $datatable->buildDatatable();
        //$form = $this->get('form.factory')->create(FiltreExtractionType::class);

        if ($request->isXmlHttpRequest() || 'UcaGest_ReportingCreditRecherche' == $request->get('_route')) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }

        $twigConfig = [
            'datatable' => $datatable,
            'codeListe' => 'UtilisateurCreditHistorique',
            //'form' => $form->createView(),
            'noAddButton' => true,
            // 'endDate' => (new \DateTime('now'))->format('Y-m-d'),
            //'startDate' => substr($repo->minDateCredit(), 0, strpos($repo->minDateCredit(), ' ')),
        ];

        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($usr->hasRole('ROLE_GESTION_EXTRACTION')) {
            $twigConfig['gestionButtons'] = true;
            $twigConfig['exportAll'] = true;
        }

        return $this->render('@Uca/UcaGest/Reporting/UtilisateurCreditHistorique/Datatable.html.twig', $twigConfig);
    }

    /** @Route("/Extraction/{dateDebut}/{dateFin}/{nom}/{prenom}/{recherche}/{operation}/{statut}/{montant}" , name="UcaGest_ReportingCreditExtraction", options={"expose"=true})*/
    public function creditsExtractionAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $recherche, $operation, $statut, $montant)
    {
        $dateDebut = 'null' !== $dateDebut ? \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : null;
        $dateFin = 'null' !== $dateFin ? \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : null;
        'null' != $nom ?: $nom = null;
        'null' != $prenom ?: $prenom = null;
        'null' != $recherche ?: $recherche = null;
        'null' != $operation ?: $operation = null;
        'null' != $montant ?: $montant = null;
        'null' != $statut ?: $statut = null;

        $em = $this->getDoctrine()->getManager();
        $listeCredit = $em->getRepository(UtilisateurCreditHistorique::class)
            ->findExtractedCredits($dateDebut, $dateFin, $nom, $prenom, $recherche, $operation, $statut, $montant, true)
        ;

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
                5 => $translator->trans('utilisateur.credit.operation'),
                6 => $translator->trans('common.statut'),
                7 => $translator->trans('utilisateur.credit.typeoperation'),
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
                $extractionService->setWorksheet($worksheet, $datas, ['I']);
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

        $this->get('uca.flashbag')->addMessageFlashBag('common.aucune.facture', 'danger');

        return $this->redirectToRoute('UcaGest_ReportingCredit');
    }

    /** @Route("/ExportAll/{dateDebut}/{dateFin}/{nom}/{prenom}/{recherche}/{operation}/{statut}/{montant}", name="UcaGest_ReportingCreditExportAll", options={"expose"=true})*/
    public function exportAllAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $recherche, $operation = null, $statut, $montant)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(UtilisateurCreditHistorique::class);

        $dateDebut = 'null' !== $dateDebut ? \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : null;
        $dateFin = 'null' !== $dateFin ? \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : null;
        'null' != $nom ?: $nom = null;
        'null' != $prenom ?: $prenom = null;
        'null' != $recherche ?: $recherche = null;
        'null' != $statut ?: $statut = null;
        'null' != $montant ?: $montant = null;

        $credits = $repo->findExtractedCredits($dateDebut, $dateFin, $nom, $prenom, $recherche, 'Ajout manuel de crédit', $statut, $montant, false);
        $avoirs = $repo->findExtractedCredits($dateDebut, $dateFin, $nom, $prenom, $recherche, ["Génération d'avoir", "Report d'avoir"], $statut, $montant, false);

        if (!empty($avoirs) || !empty($credits)) {
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
