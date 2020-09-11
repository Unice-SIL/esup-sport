<?php

/*
 * Classe - ReportingCommandes
 *
 * Gestion des actions liees au Reporting
 * Reporting des commandes et des crédit
 * Gestion de l'export des fichier en $pdf
 * Gestion des avoirs
*/

namespace UcaBundle\Controller\UcaGest\Gestion;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as FormatCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\DetailsCommandeDatatable;
use UcaBundle\Datatables\GestionCommandesDatatable;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\Parametrage;
use UcaBundle\Entity\UtilisateurCreditHistorique;
use UcaBundle\Form\GestionCommandeType;

/**
 * @Route("UcaGest/Reporting")
 * @Isgranted("ROLE_GESTION_COMMANDES")
 */
class ReportingController extends Controller
{
    /**
     * @Route("/Commandes",name="UcaGest_ReportingCommandes")
     *
     * @param null|mixed $avoir
     */
    public function listerAction(Request $request, Commande $item = null)
    {
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(GestionCommandesDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        $form = $this->get('form.factory')->create(GestionCommandeType::class);
        $twigConfig['form'] = $form->createView();
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commande.statut NOT LIKE :panier');
            $qb->setParameter('panier', 'panier');
            /*if ('UcaGest_ReportingAvoirs' == $request->get('_route')) {
                $qb->andWhere('commande.id = :id');
                $qb->setParameter('id', $item->getId());
                $twigConfig['avoirReporting'] = true;
            }*/

            return $responseService->getResponse();
        }

        // Bouton Ajouter
        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'ReportingCommandes';

        if ($this->isGranted('ROLE_GESTION_PAIEMENT_COMMANDE') or $this->isGranted('ROLE_GESTION_COMMANDES')) {
            //Ajout du bouton exporter toutes les factures
            $twigConfig['exportAll'] = true;
            //Ajout du bouton extraire les commandes
            $twigConfig['gestionButtons'] = true;
        }

        return $this->render('@Uca/UcaGest/Reporting/Commande/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Commande/{id}",name="UcaGest_ReportingCommandeDetails", methods={"GET"})
     * @Route("/Avoir/{id}/{refAvoir}", name="UcaGest_AvoirDetails", methods={"GET"})
     *
     * @param null|mixed $refAvoir
     */
    public function voirAction(Request $request, Commande $commande, $refAvoir = null)
    {
        /*foreach ($commande->getCommandeDetails() as $cmdDetails) {
            dump($cmdDetails->getLibelle());
        }
        die;*/
        $em = $this->getDoctrine()->getManager();
        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(DetailsCommandeDatatable::class);
        $creditRepo = $em->getRepository(UtilisateurCreditHistorique::class);
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            $qb->where('commandedetail.commande = :commande');
            $qb->setParameter('commande', $commande);
            if ('UcaGest_AvoirDetails' == $request->get('_route')) {
                $qb->andWhere('commandedetail.referenceAvoir= :refAvoir');
                $qb->setParameter('refAvoir', $refAvoir);
            }

            return $responseService->getResponse();
        }

        if ($listeCartes = $commande->hasFormatAchatCarte()) {
            if ('termine' == $commande->getStatut()) {
                foreach ($listeCartes as $carteTerminee) {
                    if (null == $carteTerminee->getNumeroCarte()) {
                        $twigConfig['editCardButton'] = true;
                    }
                }
                $twigConfig['cartes'] = $listeCartes;
            } elseif ('avoir' == $commande->getStatut()) {
                $tabCartes = [];
                foreach ($listeCartes as $carteAvoir) {
                    if (null == $carteAvoir->getAvoir()) {
                        $tabCartes[] = $carteAvoir;

                        if (null == $carteAvoir->getNumeroCarte()) {
                            $twigConfig['editCardButton'] = true;
                        }
                    }
                }
                if (!empty($tabCartes)) {
                    $twigConfig['cartes'] = $tabCartes;
                }
            }
        }

        $twigConfig['noAddButton'] = true;
        $twigConfig['codeListe'] = 'DetailsCommande';
        $twigConfig['retourBouton'] = true;
        $twigConfig['commande'] = $commande;
        $twigConfig['source'] = 'reporting';

        if ('UcaGest_AvoirDetails' == $request->get('_route')) {
            $twigConfig['refAvoir'] = (int) $refAvoir;
            $creditAssocie = $creditRepo->findOneBy(['avoir' => (int) $refAvoir]);
            if ('avoir' == $commande->getStatut() && $usr->hasRole('ROLE_GESTION_CREDIT_UTILISATEUR_ECRITURE') && 'annule' == $creditAssocie->getStatut()) {
                $twigConfig['ReportButton'] = true;
            }

            return $this->render('@Uca/UcaGest/Reporting/Avoir/DetailsAvoir.html.twig', $twigConfig);
        }

        return $this->render('@Uca/UcaGest/Reporting/Commande/DetailsCommande.html.twig', $twigConfig);
    }

    /**
     * @Route("/{id}/Avoir/Ajouter", name="UcaGest_AvoirAjouter", options={"expose"=true}, methods={"GET", "POST"}, requirements={"id"="\d+"})
     * @Isgranted("ROLE_GESTION_AVOIR")
     */
    public function ajouterAvoirAction(Request $request, Commande $item)
    {
        $em = $this->getDoctrine()->getManager();
        $montant = 0;
        $oldRef = $em->getRepository('UcaBundle:CommandeDetail')->max('referenceAvoir');
        $form = $this->createForm('UcaBundle\Form\AvoirType', $item);
        $form->handleRequest($request);
        $dtAvoir = new \DateTime();
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $this->get('uca.flashbag')->addMessageFlashBag('avoir.ajouter.success', 'success');
            foreach ($item->getAvoirCommandeDetails() as $cmdDetails) {
                $montant += $cmdDetails->getMontant();
                $cmdDetails
                    ->setReferenceAvoir($oldRef + 1)
                    ->setAvoir($item)
                    ->setDateAvoir($dtAvoir)
                ;
                if ($cmdDetails->getTypeAutorisation()) {
                    $commandes = $em->getRepository(Commande::class)->findCommandeByTypeAutorisationAndUser($cmdDetails->getTypeAutorisation(), $cmdDetails->getCommande()->getUtilisateur()->getId());
                    foreach ($commandes as $commande) {
                        foreach ($commande->getCommandeDetails() as $cd) {
                            if ($cd->getInscription()) {
                                $cd->getInscription()->setStatut('ancienneinscription');
                            }
                        }
                    }
                    $em->flush();
                } elseif ($cmdDetails->getInscription()) {
                    $cmdDetails->getInscription()->setStatut('ancienneinscription');
                    $em->flush();
                }
            }
            $usr = ($item->getUtilisateur());
            $credit = new UtilisateurCreditHistorique($usr, $montant, $oldRef + 1, 'credit', "Génération d'avoir");
            $em->persist($credit);
            $credit->setCommandeAssociee($item->getId());
            $usr->AddCredit($credit);
            $item->changeStatut('avoir');
            $em->flush();

            return  $this->redirectToRoute('UcaGest_ReportingCommandes');
        }

        $twigConfig['commande'] = $item;
        $twigConfig['form'] = $form->createView();
        $twigConfig['codeListe'] = 'ClasseActivite';

        return $this->render('@Uca/UcaGest/Reporting/Avoir/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("ExtractionExcel/{dateDebut}/{dateFin}", name="UcaWeb_MesCommandesExtraire", options={"expose"=true})
     * @Security("is_granted('ROLE_GESTION_COMMANDES')")
     *
     * @param null|mixed $dateDebut
     * @param null|mixed $dateFin
     */
    public function exctractAction(Request $request, $dateDebut = null, $dateFin = null)
    {
        $em = $this->getDoctrine()->getManager();

        'null' !== $dateDebut ? $dateDebut = \DateTime::createFromFormat('Y-m-d', $dateDebut)->setTime(0, 0, 0) : $dateDebut = null;
        'null' !== $dateFin ? $dateFin = \DateTime::createFromFormat('Y-m-d', $dateFin)->setTime(23, 59, 59) : $dateFin = null;

        $commandes = $em->getRepository(CommandeDetail::class)->findCommandeDetails($dateDebut, $dateFin, false);
        $commandesNonGratuites = $em->getRepository(CommandeDetail::class)->findCommandeDetails($dateDebut, $dateFin, true);

        $translator = $this->get('translator');

        if (!empty($commandesNonGratuites) && !empty($commandes)) {
            $titleColumn = [
                $translator->trans('common.nomencaisseur'),
                $translator->trans('common.prenomencaisseur'),
                $translator->trans('common.numerorecu'),
                $translator->trans('commande.numero.commande'),
                $translator->trans('common.libelle'),
                $translator->trans('commandedetail.informationscarte.numero'),
                $translator->trans('commande.paybox'),
                $translator->trans('common.cb'),
                $translator->trans('common.espece'),
                $translator->trans('common.cheque'),
                $translator->trans('commande.numero.cheque'),
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

                if (null != $dateDebut and null != $dateFin) {
                    $sheet->setCellValue('A6', $worksheet.' du '.$dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y'));
                } elseif (null != $dateDebut and null == $dateFin) {
                    $sheet->setCellValue('A6', $worksheet.' depuis le '.$dateDebut->format('d/m/Y'));
                } elseif (null == $dateDebut and null != $dateFin) {
                    $sheet->setCellValue('A6', $worksheet.' jusqu\'au '.$dateFin->format('d/m/Y'));
                } else {
                    $sheet->setCellValue('A6', $worksheet.' toutes périodes');
                }

                //Définition des titres des colonnes
                $index = 0;
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

    /**
     * @Route("ExportAll/{datePaiement}/{recherche}",name="UcaWeb_MesCommandesExportAll", options={"expose"=true})
     * @Security("is_granted('ROLE_GESTION_COMMANDES')")
     *
     * @param null|mixed $datePaiement
     * @param null|mixed $recherche
     */
    public function exportAllAction(Request $request, $datePaiement = null, $recherche = null)
    {
        $em = $this->getDoctrine()->getManager();
        'null' !== $datePaiement ?: $datePaiement = null;
        'null' !== $recherche ?: $recherche = null;
        $parametrage = $em->getRepository(Parametrage::class)->findOneById(1);
        $commandes = $em->getRepository(Commande::class)->findAllFacture($datePaiement, $recherche);
        $i = 0;
        foreach ($commandes as $commande) {
            $content[$i] = $this->renderView('@Uca/UcaWeb/Commande/Facture.html.twig', ['commande' => $commande, 'parametrage' => $parametrage]);
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

    public function createWorksheet($listeCommandeDetail, $sheet)
    {
        $cmd = [];
        $row = 8;
        $styleArray = $this->setStyleArrayForExcel(false, 10);
        //Définitions des lignes de commandes
        foreach ($listeCommandeDetail as $detail) {
            $commande = $detail->getCommande();
            $numeroCheque = $commande->getNumeroCheque();
            $cmd = [
                0 => $commande->getNomEncaisseur(),
                1 => $commande->getPrenomEncaisseur(),
                2 => $commande->getNumeroRecu(),
                3 => $commande->getNumeroCommande(),
                4 => $detail->getLibelle(),
                5 => $detail->getNumeroCarte(),
                6 => '',
                7 => '',
                8 => '',
                9 => '',
                10 => '',
            ];

            $montant = $detail->getMontant(); //.'€';
            switch ($commande->getTypePaiement()) {
                case 'BDS':
                    switch ($commande->getMoyenPaiement()) {
                        case 'cb': $cmd[7] = $montant;

                        break;
                        case 'espece': $cmd[8] = $montant;

                        break;
                        case 'cheque':
                            $cmd[9] = $montant;
                            $cmd[10] = $numeroCheque;

                        break;
                        case null:
                        break;
                    }

                break;
                case 'PAYBOX': $cmd[6] = $montant;

                break;
                case 'NA':

                break;
                case null:
                break;
            }

            if (!empty($cmd)) {
                $column = 'A';

                foreach ($cmd as $data) {
                    $sheet->setCellValue($column.$row, $data);
                    if (in_array($column, range('G', 'J'))) {
                        $sheet->getStyle($column.$row)->getNumberFormat()->setFormatCode(FormatCell::FORMAT_CURRENCY_EUR_SIMPLE);
                    }
                    $sheet->getStyle($column.$row)->applyFromArray($styleArray);
                    ++$column;
                }
                ++$row;
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
     * @Route("/Commande/InformationsCarte/{id}/Modifier", name="UcaGest_CommandeDetails_InformationsCarte", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    public function modifierInformationsCarteAction(Request $request, CommandeDetail $item)
    {
        $commande = $item->getCommande();
        if (!$commande->hasFormatAchatCarte()) {
            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $item->getId()]);
        }
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm('UcaBundle\Form\CommandeDetailInformationsCarteType', $item, [
            'date_paiement' => $commande->getDatePaiement(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');

            return $this->redirectToRoute('UcaGest_ReportingCommandeDetails', ['id' => $commande->getId()]);
        }

        $twigConfig['commandeDetail'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('@Uca/UcaGest/Reporting/Commande/FormulaireNumeroCarte.html.twig', $twigConfig);
    }
}
