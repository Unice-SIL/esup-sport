<?php
/*
 * Classe - ExtractionExcelService:
 *
 * Permet
*/

namespace App\Service\Service;

use App\Entity\Uca\DhtmlxEvenement;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as FormatCell;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExtractionExcelService
{
    private $spreadsheet;
    private $translator;
    private $writer;

    public function __construct(TranslatorInterface $translator)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->getSpreadsheet());
        $this->translator = $translator;
    }

    public function setWorksheets(array $worksheetsList, $dateDebut, $dateFin)
    {
        $this->spreadsheet->removeSheetByIndex(0);
        $headerRow = 7;

        foreach ($worksheetsList as $title => $colHeaders) {
            $worksheet = new Worksheet();
            $worksheet
                ->setTitle($title)
                ->setCellValue('E3', 'DVU Sport')
            ;
            $logo = new Drawing();
            $logo
                ->setName('Logo')
                ->setPath('build/images/logo-UCA-large-transp.png')
                ->setCoordinates('A1')
                ->setWorksheet($worksheet, true)
            ;
            $this->spreadsheet->addSheet($worksheet);
            if (null != $dateDebut and null != $dateFin) {
                $worksheet->setCellValue('A6', $title.' du '.$dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y'));
            } elseif (null != $dateDebut and null == $dateFin) {
                $worksheet->setCellValue('A6', $title.' depuis le '.$dateDebut->format('d/m/Y'));
            } elseif (null == $dateDebut and null != $dateFin) {
                $worksheet->setCellValue('A6', $title.' jusqu\'au '.$dateFin->format('d/m/Y'));
            } else {
                $worksheet->setCellValue('A6', $title.' toutes périodes');
            }
            $styleArray = $this->setStyleArrayForExcel(true, count($colHeaders));
            $col = 'A';
            foreach ($colHeaders as $colHeader) {
                $worksheet->getColumnDimension($col)->setAutoSize(true);
                $worksheet->setCellValue($col.$headerRow, $colHeader);
                $worksheet->getStyle($col.$headerRow)->applyFromArray($styleArray);
                ++$col;
            }
        }

        return $this;
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

    public function setWorksheet(Worksheet $worksheet, array $datas, $colMontant = null)
    {
        $firstRow = 8;
        $range = range('A', 'Z');
        $lastRow = $firstRow + sizeof($datas) - 1;
        $firstCell = 'A'.$firstRow;
        $lastCell = $range[sizeof($datas[0]) - 1].$lastRow;

        if (!empty($datas)) {
            $styleArray = $this->setStyleArrayForExcel(false, count($datas[0]));
            $worksheet->fromArray($datas, null, $firstCell, false);
            $worksheet->getStyle($firstCell.':'.$lastCell)->applyFromArray($styleArray);
            if (null !== $colMontant) {
                foreach ($colMontant as $col) {
                    $worksheet->getStyle($col.$firstRow.':'.$col.$lastRow)->getNumberFormat()->setFormatCode(FormatCell::FORMAT_CURRENCY_EUR_SIMPLE);
                }
            }
        }

        return $this;
    }

    public function setReportingCommandeWorksheetsHeaders(array $worksheets, $dateDebut, $dateFin)
    {
        foreach ($worksheets as $title => $worksheet) {
            $dataHeader[$title] = [
                0 => $this->translator->trans('common.nomacheteur'),
                1 => $this->translator->trans('common.prenomacheteur'),
                2 => $this->translator->trans('common.nomencaisseur'),
                3 => $this->translator->trans('common.prenomencaisseur'),
                4 => $this->translator->trans('common.datepaiement'),
                5 => $this->translator->trans('common.numerorecu'),
                6 => $this->translator->trans('commande.numero.commande'),
                7 => $this->translator->trans('common.libelle'),
                8 => $this->translator->trans('commandedetail.informationscarte.numero'),
                9 => $this->translator->trans('commande.paybox'),
                10 => $this->translator->trans('common.credit'),
                11 => $this->translator->trans('common.cb'),
                12 => $this->translator->trans('common.espece'),
                13 => $this->translator->trans('common.cheque'),
                14 => $this->translator->trans('commande.numero.cheque'),
            ];
        }

        return $this->setWorksheets($dataHeader, $dateDebut, $dateFin);
    }

    public function setReportingCommandeWorksheetsDatas(array $worksheets)
    {
        foreach ($worksheets as $title => $cmdDetails) {
            $worksheet = $this->getWorksheetByTitle($title);
            $data = [];
            foreach ($cmdDetails as $cmdDetail) {
                $cmd = [
                    0 => null != $cmdDetail->getCommande()->getUtilisateur() ? $cmdDetail->getCommande()->getUtilisateur()->getNom() : '',
                    1 => null != $cmdDetail->getCommande()->getUtilisateur() ? $cmdDetail->getCommande()->getUtilisateur()->getPrenom() : '',
                    2 => 'PAYBOX' == $cmdDetail->getCommande()->getTypePaiement() ? 'En ligne' : $cmdDetail->getCommande()->getNomEncaisseur(),
                    3 => 'PAYBOX' == $cmdDetail->getCommande()->getTypePaiement() ? 'En ligne' : $cmdDetail->getCommande()->getPrenomEncaisseur(),
                    4 => null != $cmdDetail->getCommande()->getDatePaiement() ? $cmdDetail->getCommande()->getDatePaiement()->format('d/m/Y') : '',
                    5 => $cmdDetail->getCommande()->getNumeroRecu(),
                    6 => $cmdDetail->getCommande()->getNumeroCommande(),
                    7 => $cmdDetail->getLibelle(),
                    8 => $cmdDetail->getNumeroCarte(),
                    9 => '',
                    10 => '',
                    11 => '',
                    12 => '',
                    13 => '',
                    14 => '',
                ];
                $montant = $cmdDetail->getMontant();

                if ('BDS' == $cmdDetail->getCommande()->getTypePaiement()) {
                    switch ($cmdDetail->getCommande()->getMoyenPaiement()) {
                        case 'cb': $cmd[11] = $montant;

                        break;

                        case 'espece': $cmd[12] = $montant;

                        break;

                        case 'cheque':
                            $cmd[13] = $montant;
                            $cmd[14] = $cmdDetail->getCommande()->getNumeroCheque();

                        break;

                        case null:
                        break;
                    }
                } elseif ('PAYBOX' == $cmdDetail->getCommande()->getTypePaiement()) {
                    $cmd[9] = $montant;
                } elseif ('credit' == $cmdDetail->getCommande()->getTypePaiement()) {
                    $cmd[10] = $montant;
                } elseif ('NA' == $cmdDetail->getCommande()->getTypePaiement()) {
                } elseif (null == $cmdDetail->getCommande()->getTypePaiement()) {
                }
                $data[] = $cmd;
            }
            if (!empty($data)) {
                $this->setWorksheet($worksheet, $data, ['J', 'K', 'L', 'M', 'N']);
            }
        }
    }

    public function getExtractionReportingCommande(array $worksheets, $dateDebut, $dateFin)
    {
        $this->setReportingCommandeWorksheetsHeaders($worksheets, $dateDebut, $dateFin);
        $this->setReportingCommandeWorksheetsDatas($worksheets);

        return $this;
    }

    public function getWorksheetByTitle($title)
    {
        foreach ($this->getSpreadsheet()->getAllSheets() as $worksheet) {
            if ($title == $worksheet->getTitle()) {
                return $worksheet;
            }
        }

        return false;
    }

    public function getWriter()
    {
        return $this->writer;
    }

    public function getSpreadsheet()
    {
        return $this->spreadsheet;
    }

    public function getExtractionListeInscription(DhtmlxEvenement $dhtmlxEvenement) {
        $inscriptions = [];
        $eventName = '';

        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getAllInscriptions();
                $eventName = $dhtmlxEvenement->getSerie()->getCreneau()->getFormatActivite()->getActivite()->getLibelle();
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getAllInscriptions();
            $eventName = $dhtmlxEvenement->getFormatSimple()->getActivite()->getLibelle();
        }

        $columnName = ['Nom', 'Prénom', 'Téléphone', 'Statut'];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheet = new Worksheet();
        $sheet->setTitle('Inscriptions');
        $sheet->setCellValue('A6', $eventName.' du '.$dhtmlxEvenement->getDateDebut()->format('d/m/Y H:i').' - '.$dhtmlxEvenement->getDateFin()->format('d/m/Y H:i'));
        $sheet->mergeCells('A6:F6');

        $this->createExcelHeader($spreadsheet, $sheet, $columnName);

        $styleArray = $this->setStyleArrayForExcel(false, 10);
        $idCol = 8;

        if (null != $inscriptions && sizeof($inscriptions) > 0) {
            foreach ($inscriptions as $inscription) {
                $utilisateur = $inscription->getUtilisateur();
                $dataLine = [
                    $utilisateur->getNom(),
                    $utilisateur->getPrenom(),
                    $utilisateur->getTelephone() ?? '',
                    $inscription->getStatut() == 'valide' ? $this->translator->trans('formatSimple.list.inscrit') : $this->translator->trans('creneau.list.preinscrit')
                ];

                if (!empty($dataLine)) {
                    $index = 0;
                    foreach (range('A', 'D') as $col) {
                        $sheet->setCellValue($col.$idCol, $dataLine[$index]);
                        $sheet->getStyle($col.$idCol)->applyFromArray($styleArray);
                        ++$index;
                    }
                    ++$idCol;
                }
            }
        }

        return $this->createExcelStreamedResponse($spreadsheet, 'Inscription '.$eventName.' ');
    }

    //Function permettant la création du fichier Excel
    private function createExcelHeader($spreadsheet, $sheet, $titleColumn)
    {
        $styleArray = $this->setStyleArrayForExcel(true, 10);

        $gdImage = imagecreatetruecolor(1,1);
        try {
            $gdImage = imagecreatefrompng('build/images/logo-UCA-large-transp.png');
            imagesavealpha($gdImage,true);
        } catch (\Exception $e) {}
        //En-tête du fichier excel
        // $logo = new Drawing();
        $logo = new MemoryDrawing();
        $logo->setName('Logo');
        $logo->setDescription('Logo');
        // $logo->setPath('build/images/logo-UCA-large-transp.png');
        $logo->setImageResource($gdImage);
        $logo->setMimeType(MemoryDrawing::MIMETYPE_PNG);
        $logo->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
        $logo->setCoordinates('A1');
        $logo->setWorksheet($sheet, true);

        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);

        $spreadsheet->addSheet($sheet);
        $sheet->setCellValue('F3', 'DVU Sport');

        $index = 0;
        $alphabet = range('A', 'Z');
        $premiereLettre = 0;
        $deuxiemeLettre = 0;
        $currentLettre = 0;

        for ($i = 0; $i < sizeof($titleColumn); ++$i) {
            if ($i < 26) {
                $sheet->setCellValue($alphabet[$i].'7', $titleColumn[$index]);
                $sheet->getStyle($alphabet[$i].'7')->applyFromArray($styleArray);
                $sheet->getColumnDimension($alphabet[$i])->setAutoSize(true);
                $currentLettre = $i;
            } else {
                $sheet->setCellValue($alphabet[$premiereLettre].$alphabet[$deuxiemeLettre].'7', $titleColumn[$index]);
                $sheet->getStyle($alphabet[$premiereLettre].$alphabet[$deuxiemeLettre].'7')->applyFromArray($styleArray);
                $sheet->getColumnDimension($alphabet[$premiereLettre].$alphabet[$deuxiemeLettre])->setAutoSize(true);
                $currentLettre = [$premiereLettre, $deuxiemeLettre];
                ++$deuxiemeLettre;
                if (26 == $deuxiemeLettre) {
                    $deuxiemeLettre = 0;
                    ++$premiereLettre;
                }
            }

            //On freeze la top bar pour garder le nom des colonnes au scroll
            $sheet->freezePane('A8');

            ++$index;
        }
    }

    private function createExcelStreamedResponse($spreadsheet, $extract_name)
    {
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $filename = $extract_name.date('Y-m-d').'_'.date('H-i-s').'.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);

        return $response;
    }
}
