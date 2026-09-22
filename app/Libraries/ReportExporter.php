<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Renders a report array (as built by App\Libraries\Reports::build()) to PDF or XLSX.
 * CSV export stays in the Reports controller - this class only handles the two richer formats.
 */
class ReportExporter
{
    private const BRAND = '#007C9C';

    /** @return string raw PDF bytes */
    public static function pdf(array $report, string $slug): string
    {
        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);   // no network fetches - keeps report generation fast and self-contained
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml(view('reports/pdf', ['report' => $report, 'slug' => $slug]));
        $dompdf->render();

        return $dompdf->output();
    }

    /** @return string path to a temporary .xlsx file (caller deletes it after streaming) */
    public static function xlsx(array $report, string $slug): string
    {
        $book  = new Spreadsheet();
        $book->getProperties()->setCreator(org('name'))->setTitle($report['title'])->setSubject('Satvikdaan report export');

        self::summarySheet($book->getActiveSheet(), $report);
        self::dataSheet($book, $report);

        $book->setActiveSheetIndex(0);
        $path = sys_get_temp_dir() . '/satvikdaan-' . $slug . '-' . bin2hex(random_bytes(6)) . '.xlsx';
        (new Xlsx($book))->save($path);

        return $path;
    }

    private static function summarySheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $report): void
    {
        $sheet->setTitle('Summary');
        $sheet->setCellValue('A1', org('name'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', $report['title'] . ' report');
        $sheet->setCellValue('A3', $report['description']);
        $sheet->setCellValue('A4', 'Generated ' . date('d M Y H:i'));
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true);
        $sheet->getStyle('A3:A4')->getFont()->setSize(9)->getColor()->setRGB('666666');

        $row = 6;
        $sheet->setCellValue("A{$row}", 'Metric');
        $sheet->setCellValue("B{$row}", 'Value');
        self::headerRow($sheet, "A{$row}:B{$row}");
        $row++;
        foreach ($report['kpis'] as [$label, $val, $type]) {
            $sheet->setCellValue("A{$row}", $label);
            $cell = "B{$row}";
            $sheet->setCellValue($cell, $val);
            match ($type) {
                'money' => $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00'),
                'pct'   => $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('0.0"%"'),
                'int'   => $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0'),
                default => null,
            };
            $row++;
        }
        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(18);
    }

    private static function dataSheet(Spreadsheet $book, array $report): void
    {
        $sheet = $book->createSheet();
        $sheet->setTitle(mb_substr($report['table_title'], 0, 31) ?: 'Detail');

        foreach ($report['headers'] as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }
        self::headerRow($sheet, $sheet->calculateWorksheetDimension());

        $r = 2;
        foreach ($report['rows'] as $row) {
            foreach ($row as $i => $val) {
                $col  = $i + 1;
                $type = $report['formats'][$i] ?? 'text';
                // Guard against spreadsheet formula injection, same rule as the CSV export.
                if (is_string($val) && $val !== '' && strpbrk($val[0], "=+-@\t\r") !== false) {
                    $val = "'" . $val;
                }
                $sheet->setCellValueByColumnAndRow($col, $r, $val);
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                match ($type) {
                    'money' => $sheet->getStyle("{$letter}{$r}")->getNumberFormat()->setFormatCode('#,##0.00'),
                    'pct'   => $sheet->getStyle("{$letter}{$r}")->getNumberFormat()->setFormatCode('0.0"%"'),
                    'int'   => $sheet->getStyle("{$letter}{$r}")->getNumberFormat()->setFormatCode('#,##0'),
                    'date'  => $sheet->getStyle("{$letter}{$r}")->getNumberFormat()->setFormatCode('dd mmm yyyy'),
                    default => null,
                };
            }
            $r++;
        }
        foreach (range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($report['headers']))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');
    }

    private static function headerRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('007C9C');
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }
}
