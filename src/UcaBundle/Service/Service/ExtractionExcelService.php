<?php
/*
 * Classe - ExtractionExcelService:
 *
 * Permet
*/

namespace UcaBundle\Service\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as FormatCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Translation\TranslatorInterface;

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
            $this->setWorksheet($worksheet, $data, ['G', 'H', 'I', 'J', 'K']);
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
}