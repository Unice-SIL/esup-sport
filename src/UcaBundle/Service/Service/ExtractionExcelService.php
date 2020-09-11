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

class ExtractionExcelService
{
    private $spreadsheet;
    private $writer;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->writer = new Xlsx($this->getSpreadsheet());
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
        $row = 8;

        if (!empty($datas)) {
            $styleArray = $this->setStyleArrayForExcel(false, count($datas[0]));
            $col = 'A';
            foreach ($datas as $data) {
                foreach ($data as $cell) {
                    $worksheet->setCellValue($col.$row, $cell);
                    if (null !== $colMontant && $col == $colMontant) {
                        $worksheet->getStyle($col.$row)->getNumberFormat()->setFormatCode(FormatCell::FORMAT_CURRENCY_EUR_SIMPLE);
                    }
                    $worksheet->getStyle($col.$row)->applyFromArray($styleArray);
                    ++$col;
                }
                $col = 'A';
                ++$row;
            }
        }

        return $this;
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
