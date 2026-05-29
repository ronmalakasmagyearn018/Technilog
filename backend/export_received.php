<?php
// backend/export_received.php
// Generates a styled .xlsx matching the Technilog Received Orders Report design.

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

$useXlsx = false;
foreach ([__DIR__ . '/vendor/autoload.php', __DIR__ . '/../vendor/autoload.php'] as $path) {
    if (file_exists($path)) {
        require_once $path;
        $useXlsx = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
        break;
    }
}

$monthLabel = $_POST['month_label'] ?? 'Monthly Records';
$rowsJson   = $_POST['rows_json']   ?? '[]';
$rows       = json_decode($rowsJson, true);

if (!is_array($rows) || empty($rows)) {
    http_response_code(400);
    echo 'No data to export.';
    exit;
}

$filename = 'Technilog_Received_' . str_replace(' ', '_', $monthLabel);

// ── Colours ──────────────────────────────────────────────────────
$DARK_BLUE   = '2E279D';  // title bar
$MID_BLUE    = '4D3FCC';  // header row
$LIGHT_BLUE  = 'EEEEFF';  // subtitle
$ROW_EVEN    = 'F0F0FF';
$ROW_ODD     = 'FFFFFF';
$TOTAL_ROW   = '2E279D';
$WHITE       = 'FFFFFF';
$TEXT_DARK   = '1A1A2E';
$TEXT_SUBTLE = '444466';

// ── Column definitions ───────────────────────────────────────────
// [letter, header label, width]
$cols = [
    ['A', '#',           5 ],
    ['B', 'Order Ref',   14],
    ['C', 'Customer',    20],
    ['D', 'Email',       30],
    ['E', 'Phone',       14],
    ['F', 'Items',       38],
    ['G', 'Subtotal',    13],
    ['H', 'Shipping',    11],
    ['I', 'Total',       13],
    ['J', 'Payment',     11],
    ['K', 'Day',         16],
    ['L', 'Received At', 22],
];
$lastCol = 'L';
$range   = "A:{$lastCol}";

if ($useXlsx) {
    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($monthLabel, 0, 31));

    // ── Row 1: Title ─────────────────────────────────────────────
    $sheet->mergeCells("A1:{$lastCol}1");
    $sheet->setCellValue('A1', 'TECHNILOG — Received Orders Report');
    $sheet->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $WHITE]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $DARK_BLUE]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(1)->setRowHeight(34);

    // ── Row 2: Subtitle ──────────────────────────────────────────
    $sheet->mergeCells("A2:{$lastCol}2");
    $sheet->setCellValue('A2',
        'Period: ' . $monthLabel . '   |   Generated: ' . date('F j, Y  g:i A'));
    $sheet->getStyle('A2')->applyFromArray([
        'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => $TEXT_SUBTLE]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $LIGHT_BLUE]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getRowDimension(2)->setRowHeight(20);

    // ── Row 3: Headers ───────────────────────────────────────────
    foreach ($cols as [$letter, $label, $width]) {
        $sheet->setCellValue("{$letter}3", $label);
        $sheet->getColumnDimension($letter)->setWidth($width);
    }
    $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
        'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $WHITE]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $MID_BLUE]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => false],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                         'color'       => ['rgb' => 'CCCCCC']]],
    ]);
    $sheet->getRowDimension(3)->setRowHeight(22);

    // ── Data rows ────────────────────────────────────────────────
    $totalRevenue = 0;
    foreach ($rows as $i => $row) {
        $r  = $i + 4;
        $bg = ($i % 2 === 0) ? $ROW_EVEN : $ROW_ODD;

        $sheet->setCellValue("A{$r}", $row['no']);
        $sheet->setCellValue("B{$r}", $row['order_ref']);
        $sheet->setCellValue("C{$r}", $row['customer']);
        $sheet->setCellValue("D{$r}", $row['email']);

        // Phone: set as string so Excel never converts to scientific notation
        $sheet->setCellValueExplicit("E{$r}", $row['phone'],
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue("F{$r}", $row['items']);
        $sheet->setCellValue("G{$r}", floatval($row['subtotal']));
        $sheet->setCellValue("H{$r}", floatval($row['shipping']));
        $sheet->setCellValue("I{$r}", floatval($row['total']));
        $sheet->setCellValue("J{$r}", $row['payment']);
        $sheet->setCellValue("K{$r}", $row['day']);
        $sheet->setCellValue("L{$r}", $row['received_at']);

        $totalRevenue += floatval($row['total']);

        // Peso number format for money columns
        foreach (['G','H','I'] as $mc) {
            $sheet->getStyle("{$mc}{$r}")
                  ->getNumberFormat()
                  ->setFormatCode('"P"#,##0.00');
        }

        // Row styling
        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID,
                          'startColor' => ['rgb' => $bg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                           'color'       => ['rgb' => 'E0E0EE']]],
            'font'    => ['size' => 10],
        ]);

        // Centre the # and number-ish columns
        foreach (['A','G','H','I','J','K'] as $cc) {
            $sheet->getStyle("{$cc}{$r}")
                  ->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Wrap items column
        $sheet->getStyle("F{$r}")->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($r)->setRowHeight(-1); // auto-height
    }

    // ── Totals row ───────────────────────────────────────────────
    $totRow = count($rows) + 4;
    $sheet->mergeCells("A{$totRow}:F{$totRow}");
    $sheet->setCellValue("A{$totRow}", 'TOTAL — ' . count($rows) . ' order(s)');
    $sheet->setCellValue("I{$totRow}", $totalRevenue);
    $sheet->getStyle("I{$totRow}")->getNumberFormat()->setFormatCode('"P"#,##0.00');
    $sheet->getStyle("A{$totRow}:{$lastCol}{$totRow}")->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE]],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $TOTAL_ROW]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $sheet->getStyle("A{$totRow}")->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("I{$totRow}")->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($totRow)->setRowHeight(24);

    // ── Freeze panes below header ────────────────────────────────
    $sheet->freezePane('A4');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');

    (new Xlsx($spreadsheet))->save('php://output');
    exit;
}

// ── CSV fallback (no PhpSpreadsheet) ─────────────────────────────
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
fputcsv($out, ['#','Order Ref','Customer','Email','Phone','Items','Subtotal','Shipping','Total','Payment','Day','Received At']);
$totalRevenue = 0;
foreach ($rows as $row) {
    fputcsv($out, [
        $row['no'], $row['order_ref'], $row['customer'], $row['email'],
        $row['phone'], $row['items'],
        number_format(floatval($row['subtotal']), 2),
        number_format(floatval($row['shipping']), 2),
        number_format(floatval($row['total']),    2),
        $row['payment'], $row['day'], $row['received_at'],
    ]);
    $totalRevenue += floatval($row['total']);
}
fputcsv($out, ['TOTAL', '', count($rows).' order(s)', '', '', '', '', '',
               number_format($totalRevenue, 2), '', '', '']);
fclose($out);
exit;