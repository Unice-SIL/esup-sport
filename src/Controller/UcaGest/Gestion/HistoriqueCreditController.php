<?php
/*
 * Classe - HistoriqueCreditController
 *
 * Gestion de l'historique des crédits utilisateur
*/

namespace App\Controller\UcaGest\Gestion;

use App\Entity\Uca\Commande;
use App\Service\Common\FlashBag;
use App\Form\FiltreExtractionType;
use App\Service\Common\CreationPdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Uca\UtilisateurCreditHistorique;
use App\Service\Service\ExtractionExcelService;
use Symfony\Component\Routing\Annotation\Route;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Datatables\UtilisateurCreditHistoriqueDatatable;
use App\Repository\UtilisateurCreditHistoriqueRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("UcaGest/Reporting/Credits")
 * @Isgranted("ROLE_GESTION_CREDIT_UTILISATEUR_LECTURE")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class HistoriqueCreditController extends AbstractController
{
    /** @Route("/" , name="UcaGest_ReportingCredit")*/
    public function listerCredit(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, UtilisateurCreditHistoriqueRepository $repo)
    {
        $datatable = $datatableFactory->create(UtilisateurCreditHistoriqueDatatable::class);
        $datatable->buildDatatable();
        //$form = $this->createForm(FiltreExtractionType::class);

        if ($request->isXmlHttpRequest() || 'UcaGest_ReportingCreditRecherche' == $request->get('_route')) {
            $responseService = $datatableResponse;
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

        $usr = $this->getUser();
        if ($usr->hasRole('ROLE_GESTION_EXTRACTION')) {
            $twigConfig['gestionButtons'] = true;
            $twigConfig['exportAll'] = true;
        }

        return $this->render('UcaBundle/UcaGest/Reporting/UtilisateurCreditHistorique/Datatable.html.twig', $twigConfig);
    }

    /** @Route("/Extraction/{dateDebut}/{dateFin}/{nom}/{prenom}/{recherche}/{operation}/{statut}/{montant}" , name="UcaGest_ReportingCreditExtraction", options={"expose"=true})*/
    public function creditsExtractionAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $recherche, $operation, $statut, $montant, FlashBag $flashBag, TranslatorInterface $translator, ExtractionExcelService $extractionService, UtilisateurCreditHistoriqueRepository $userCreditRepo)
    {
        $dateDebut = 'null' !== $dateDebut ? \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : null;
        $dateFin = 'null' !== $dateFin ? \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : null;
        'null' != $nom ?: $nom = null;
        'null' != $prenom ?: $prenom = null;
        'null' != $recherche ?: $recherche = null;
        'null' != $operation ?: $operation = null;
        'null' != $montant ?: $montant = null;
        'null' != $statut ?: $statut = null;

        $listeCredit = $userCreditRepo->findExtractedCredits($dateDebut, $dateFin, $nom, $prenom, $recherche, $operation, $statut, $montant, true);

        if (!empty($listeCredit)) {

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

        $flashBag->addMessageFlashBag('common.aucune.facture', 'danger');

        return $this->redirectToRoute('UcaGest_ReportingCredit');
    }

    /** @Route("/ExportAll/{dateDebut}/{dateFin}/{nom}/{prenom}/{recherche}/{operation}/{statut}/{montant}", name="UcaGest_ReportingCreditExportAll", options={"expose"=true})*/
    public function exportAllAction(Request $request, $dateDebut, $dateFin, $nom, $prenom, $recherche, $operation = null, $statut, $montant, FlashBag $flashBag, CreationPdf $pdf, EntityManagerInterface $em)
    {
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
            foreach ($credits as $credit) {
                $pdf->createMultipleView('UcaBundle/UcaWeb/Utilisateur/Credit.html.twig', ['credit' => $credit]);
            }
            foreach ($avoirs as $avoir) {
                $twigConfig = [
                    'commande' => $em->getReference(Commande::class, $avoir->getCommandeAssociee()),
                    'refAvoir' => $avoir->getAvoir(),
                ];
                if ("Report d'avoir" == $avoir->getOperation()) {
                    $twigConfig['reportAvoir'] = true;
                }
                $pdf->createMultipleView('UcaBundle/UcaWeb/Commande/Avoir.html.twig', $twigConfig);
            }
            $pdf->createMultiplePdf(['author' => 'Université de Nice', 'title' => 'Credit&Avoir', 'output' => 'Credit&Avoir.pdf']);
        } else {
            $flashBag->addMessageFlashBag('common.aucune.facture', 'danger');

            return $this->redirectToRoute('UcaGest_ReportingCredit');
        }
    }
}
