<?php

namespace UcaBundle\Controller\UcaWeb;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\DetailsCommandeDatatable;
use UcaBundle\Datatables\MesCommandesDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
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
        $content = $this->renderView('@Uca/UcaWeb/Commande/Facture.html.twig', ['commande' => $commande]);

        try {
            $pdf = new HTML2PDF('p', 'A4', 'fr');
            $pdf->pdf->SetAuthor('Université de Nice');
            $pdf->pdf->SetTitle('Facture');
            $pdf->writeHTML($content);
            $pdf->Output('Facture.pdf');
        } catch (HTML2PDF_exception $e) {
            die($e);
        }
    }

    /**
     *  @Route("ExtractionExcel/{dateDebut}/{dateFin}", name="UcaWeb_MesCommandesExtraire")
     *
     * @param null|mixed $dateDebut
     * @param null|mixed $dateFin
     */
    public function exctractAction(Request $request, $dateDebut = null, $dateFin = null)
    {
        $em = $this->getDoctrine()->getManager();

        $commandes = $em->getRepository(CommandeDetail::class)->findCommandeDetails($dateDebut, $dateFin, false);

        $commandesNonGratuites = $em->getRepository(CommandeDetail::class)->findCommandeDetails($dateDebut, $dateFin, true);

        $translator = $this->get('translator');

        $today = date('d-m-Y', time());
        $dateDebutTime = strtotime($dateDebut);
        $dateFinTime = strtotime($dateFin);
        $timeToday = strtotime($dateFin);
        $dateFinToday = ($dateFinTime > $timeToday || $dateFinTime == $timeToday) ? null : $dateFinTime;
        $dateDebut = \DateTime::createFromFormat('d-m-Y', $dateDebut);
        $dateFin = \DateTime::createFromFormat('d-m-Y', $dateFin);

        if (!empty($commandesNonGratuites) && !empty($commandes)) {
            $titleColumn = [
                $translator->trans('common.nomencaisseur'),
                $translator->trans('common.prenomencaisseur'),
                $translator->trans('common.numerorecu'),
                'Numéro de commande',
                'Designation',
                'N° carte',
                'Paybox',
                $translator->trans('common.cb'),
                $translator->trans('common.espece'),
                'N° chèque',
                $translator->trans('common.cheque'),
            ];

            $styleArray = $this->setStyleArrayForExcel(true, 10);

            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);
            foreach (['Fiche de caisse', 'Liste commandes'] as $worksheet) {
                $sheet = new Worksheet();

                $sheet->setTitle($worksheet);
                //En-tête du fichier excel
                $logo = new Drawing();
                $logo->setName('Logo');
                $logo->setPath('build/images/logo-UCA-large-transp.png');
                $logo->setCoordinates('A1');
                $logo->setWorksheet($sheet, true);

                $spreadsheet->addSheet($sheet);
                $sheet->setCellValue('E3', 'DVU Sport');

                if (null != $dateDebutTime and null != $dateFinToday) {
                    $sheet->setCellValue('A6', $worksheet.' du '.$dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y'));
                } elseif (null != $dateDebutTime and null == $dateFinToday) {
                    $sheet->setCellValue('A6', $worksheet.' du '.$dateDebut->format('d/m/Y').' au '.date('d/m/Y', $timeToday));
                } elseif (null == $dateDebutTime and null != $dateFinToday) {
                    $sheet->setCellValue('A6', $worksheet.' jusqu\'au '.$dateFin->format('d/m/Y'));
                } else {
                    $sheet->setCellValue('A6', $worksheet.' jusqu\'au '.date('d/m/Y', $timeToday));
                }

                $index = 0;

                //Définition des titres des colonnes
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->setCellValue($col.'7', $titleColumn[$index]);
                    $sheet->getStyle($col.'7')->applyFromArray($styleArray);
                    ++$index;
                }

                $sheet = ('Liste commandes' == $worksheet ? $this->createWorksheet($commandes, $sheet) : $this->createWorksheet($commandesNonGratuites, $sheet));
            }
            $writer = new Xlsx($spreadsheet);
            $response = new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            $filename = 'extract_commande_'.date('Y-m-d').'_'.date('H-i-s').'.xlsx';
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

            return $response;
        }

        return $this->redirectToRoute('UcaGest_ReportingCommandes');
    }

    public function createWorksheet($listeCommandeDetail, $sheet)
    {
        $cmd = [];
        $idCol = 8;
        $styleArray = $this->setStyleArrayForExcel(false, 10);
        //Définitions des lignes de commandes
        foreach ($listeCommandeDetail as $detail) {
            $commande = $detail->getCommande();
            $cmd = [$commande->getNomEncaisseur(), $commande->getPrenomEncaisseur(), $commande->getNumeroRecu(), $commande->getNumeroCommande(), $detail->getLibelle()];
            $montant = $detail->getMontant().'€';
            switch ($commande->getTypePaiement()) {
                    case 'BDS':
                        switch ($commande->getMoyenPaiement()) {
                                case 'cb':
                                    $cmd = array_merge($cmd, ['', '', $montant, '', '', '']);

                                    break;
                                case 'espece':
                                    $cmd = array_merge($cmd, ['', '', '', $montant, '', '']);

                                    break;
                                case 'cheque':
                                    $cmd = array_merge($cmd, ['', '', '', '', '', $montant]);

                                    break;
                                case null:
                                    $cmd = array_merge($cmd, ['', '', '', '', '', '', '']);

                                break;
                        }

                        break;
                    case 'PAYBOX':
                        $cmd = array_merge($cmd, ['', $montant, '', '', '', '']);

                        break;
                    case 'NA':
                        $cmd = array_merge($cmd, ['', '', '', '', '', '']);

                    break;
                    case null:
                        $cmd = array_merge($cmd, ['', '', '', '', '', '', '']);

                        break;
                }
            if (!empty($cmd)) {
                $index = 0;
                foreach (range('A', 'K') as $col) {
                    $sheet->setCellValue($col.$idCol, $cmd[$index]);
                    $sheet->getStyle($col.$idCol)->applyFromArray($styleArray);
                    ++$index;
                }
                ++$idCol;
            }
        }

        return $sheet;
    }

    public function setStyleArrayForExcel($bold, $size): array
    {
        return [
            'font' => [
                'bold' => $bold,
                'size' => $size,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
    }

    /**
     * @Route("ExportAll/{datePaiement}/{recherche}",name="UcaWeb_MesCommandesExportAll", options={"expose"=true})
     *
     * @param null|mixed $datePaiement
     * @param null|mixed $recherche
     */
    public function exportAllAction(Request $request, $datePaiement = null, $recherche = null)
    {
        $em = $this->getDoctrine()->getManager();
        $commandes = $em->getRepository('UcaBundle:Commande')->findAllFacture($datePaiement, $recherche);
        $i = 0;
        foreach ($commandes as $commande) {
            $content[$i] = $this->renderView('@Uca/UcaWeb/Commande/Facture.html.twig', ['commande' => $commande]);
            ++$i;
        }
        if (isset($content)) {
            try {
                $pdf = new HTML2PDF('p', 'A4', 'fr');
                $pdf->pdf->SetAuthor('Université de Nice');
                $pdf->pdf->SetTitle('Facture');
                for ($i = 0; $i < sizeof($content); ++$i) {
                    $pdf->writeHTML($content[$i]);
                }
                $pdf->Output('Factures.pdf');
            } catch (HTML2PDF_exception $e) {
                die($e);
            }
        } else {
            $this->get('uca.flashbag')->addMessageFlashBag('common.aucune.facture', 'danger');

            return $this->redirectToRoute('UcaGest_ReportingCommandes');
        }
    }
}
