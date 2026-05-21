<?php
// backend/export_received.php
// use statements MUST be at the top of the file — PHP requirement

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Load PhpSpreadsheet autoloader
$useXlsx = false;
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $useXlsx = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
        break;
    }
}

// Read POST data
$monthLabel = $_POST['month_label'] ?? 'Monthly Records';
$rowsJson   = $_POST['rows_json']   ?? '[]';
$rows       = json_decode($rowsJson, true);

if (!is_array($rows) || empty($rows)) {
    http_response_code(400);
    echo 'No data to export.';
    exit;
}

$filename = 'Technilog_Received_' . str_replace(' ', '_', $monthLabel);

// XLSX export
if ($useXlsx) {
    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($monthLabel, 0, 31));

    // Title row
    $sheet->mergeCells('A1:L1');
    $sheet->setCellValue('A1', 'TECHNILOG — Received Orders Report');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E279D']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(30);

    // Subtitle
    $sheet->mergeCells('A2:L2');
    $sheet->setCellValue('A2', 'Period: ' . $monthLabel . '   |   Generated: ' . date('F j, Y  g:i A'));
    $sheet->getStyle('A2')->applyFromArray([
        'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '444466']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(18);

    // Headers
    $colLetters = ['A','B','C','D','E','F','G','H','I','J','K','L'];
    $headers    = ['#','Order Ref','Customer','Email','Phone','Items','Subtotal','Shipping','Total','Payment','Day','Received At'];
    foreach ($headers as $idx => $h) {
        $sheet->setCellValue($colLetters[$idx] . '3', $h);
    }
    $sheet->getStyle('A3:L3')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4D3FCC']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);
    $sheet->getRowDimension(3)->setRowHeight(20);

    // Data rows
    $totalRevenue = 0;
    foreach ($rows as $i => $row) {
        $r = $i + 4;
        $sheet->setCellValue('A'.$r, $row['no']);
        $sheet->setCellValue('B'.$r, $row['order_ref']);
        $sheet->setCellValue('C'.$r, $row['customer']);
        $sheet->setCellValue('D'.$r, $row['email']);
        $sheet->setCellValue('E'.$r, $row['phone']);
        $sheet->setCellValue('F'.$r, $row['items']);
        $sheet->setCellValue('G'.$r, floatval($row['subtotal']));
        $sheet->setCellValue('H'.$r, floatval($row['shipping']));
        $sheet->setCellValue('I'.$r, floatval($row['total']));
        $sheet->setCellValue('J'.$r, $row['payment']);
        $sheet->setCellValue('K'.$r, $row['day']);
        $sheet->setCellValue('L'.$r, $row['received_at']);
        $totalRevenue += floatval($row['total']);
        foreach (['G','H','I'] as $mc) {
            $sheet->getStyle($mc.$r)->getNumberFormat()->setFormatCode('"P"#,##0.00');
        }
        $bg = ($i % 2 === 0) ? 'F5F5FF' : 'FFFFFF';
        $sheet->getStyle("A{$r}:L{$r}")->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E5E5']]],
        ]);
        $sheet->getStyle('A'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Totals row
    $totRow = count($rows) + 4;
    $sheet->mergeCells("A{$totRow}:F{$totRow}");
    $sheet->setCellValue("A{$totRow}", 'TOTAL — ' . count($rows) . ' order(s)');
    $sheet->setCellValue("I{$totRow}", $totalRevenue);
    $sheet->getStyle("I{$totRow}")->getNumberFormat()->setFormatCode('"P"#,##0.00');
    $sheet->getStyle("A{$totRow}:L{$totRow}")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E279D']],
    ]);
    $sheet->getRowDimension($totRow)->setRowHeight(22);

    // Column widths
    $widths = [5,16,22,28,15,36,13,12,13,14,16,22];
    foreach ($widths as $idx => $w) {
        $sheet->getColumnDimension($colLetters[$idx])->setWidth($w);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// CSV fallback (if PhpSpreadsheet not installed)
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

$out = fopen('php://output', 'w');
fputcsv($out, ['TECHNILOG - Received Orders Report']);
fputcsv($out, ['Period: '.$monthLabel, 'Generated: '.date('F j, Y g:i A')]);
fputcsv($out, []);
fputcsv($out, ['#','Order Ref','Customer','Email','Phone','Items','Subtotal','Shipping','Total','Payment','Day','Received At']);
$totalRevenue = 0;
foreach ($rows as $row) {
    fputcsv($out, [
        $row['no'], $row['order_ref'], $row['customer'], $row['email'],
        $row['phone'], $row['items'],
        number_format(floatval($row['subtotal']),2),
        number_format(floatval($row['shipping']),2),
        number_format(floatval($row['total']),2),
        $row['payment'], $row['day'], $row['received_at'],
    ]);
    $totalRevenue += floatval($row['total']);
}
fputcsv($out, []);
fputcsv($out, ['TOTAL','',count($rows).' order(s)','','','','','',number_format($totalRevenue,2),'','','']);
fclose($out);
exit;