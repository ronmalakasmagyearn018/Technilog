<?php
// backend/import_received.php
// Accepts either:
//   (a) multipart file upload (.xlsx / .xls)  — parsed via PhpSpreadsheet
//   (b) JSON body with { rows: [...] }         — from CSV parsed client-side

// ── Silence all PHP error output so warnings never corrupt JSON ──
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

// Start output buffer BEFORE anything else — catches any stray output
ob_start();

require_once __DIR__ . '/config.php';

// Wipe everything config.php or PHP emitted (headers, warnings, notices)
ob_clean();

// Now we own the output — force JSON
header('Content-Type: application/json; charset=utf-8');

// Catch fatal errors and still return valid JSON
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $err['message']]);
    } else {
        ob_end_flush();
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Load PhpSpreadsheet if available ─────────────────────────────
$hasSpreadsheet = false;
foreach ([__DIR__ . '/vendor/autoload.php', __DIR__ . '/../vendor/autoload.php'] as $p) {
    if (file_exists($p)) { require_once $p; $hasSpreadsheet = true; break; }
}

$rows = [];

// ── (a) File upload (.xlsx / .xls) ───────────────────────────────
if (!empty($_FILES['file']['tmp_name'])) {
    if (!$hasSpreadsheet) {
        echo json_encode(['success' => false, 'message' => 'PhpSpreadsheet is required to import Excel files.']);
        exit;
    }

    $tmpPath = $_FILES['file']['tmp_name'];
    $origName = $_FILES['file']['name'] ?? '';
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    try {
        if ($ext === 'xls') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($tmpPath);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray(null, true, true, false); // 0-indexed array
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Could not read Excel file: ' . $e->getMessage()]);
        exit;
    }

    if (count($data) < 2) {
        echo json_encode(['success' => false, 'message' => 'Excel file has no data rows.']);
        exit;
    }

    // Find the header row — scan ALL rows for one containing 'order_ref'.
    // Normalize: lowercase + trim + spaces→underscores so
    // "Order Ref" → "order_ref", "Received At" → "received_at", etc.
    $normalize = fn($v) => preg_replace('/\s+/', '_', strtolower(trim((string)$v)));

    $headerRow = null;
    $headerIdx = 0;
    foreach ($data as $i => $row) {
        $normalized = array_map($normalize, $row);
        if (in_array('order_ref', $normalized)) {
            $headerRow = $normalized;
            $headerIdx = $i;
            break;
        }
    }

    if ($headerRow === null) {
        echo json_encode(['success' => false, 'message' => 'Excel file is missing the required "Order Ref" column. Make sure you import a file exported from this system.']);
        exit;
    }

    foreach (array_slice($data, $headerIdx + 1) as $dataRow) {
        $obj = [];
        foreach ($headerRow as $colIdx => $colName) {
            if ($colName === '') continue;
            $obj[$colName] = trim(ltrim((string)($dataRow[$colIdx] ?? ''), "\t"));
        }
        if (!empty($obj['order_ref']) && $obj['order_ref'] !== '—') {
            $rows[] = $obj;
        }
    }

// ── (b) JSON body (CSV rows parsed client-side) ───────────────────
} else {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!isset($data['rows']) || !is_array($data['rows'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid payload.']);
        exit;
    }
    $rows = $data['rows'];
}

if (empty($rows)) {
    echo json_encode(['success' => false, 'message' => 'No valid rows found to import.']);
    exit;
}

// ── Editable columns (order_ref = lookup key only) ────────────────
$allowed = [
    'customer'  => 'customer_name',
    'email'     => 'customer_email',
    'phone'     => 'customer_phone',
    'subtotal'  => 'subtotal',
    'shipping'  => 'shipping',
    'total'     => 'total',
    'payment'   => 'payment_method',
];

$updated = 0;
$skipped = 0;
$errors  = [];

foreach ($rows as $idx => $row) {
    $orderRef = trim(ltrim($row['order_ref'] ?? '', "\t"));
    if ($orderRef === '' || $orderRef === '—') { $skipped++; continue; }

    $setParts  = [];
    $bindVals  = [];
    $bindTypes = '';

    foreach ($allowed as $csvKey => $dbCol) {
        if (!array_key_exists($csvKey, $row)) continue;
        $val = trim(ltrim((string)$row[$csvKey], "\t")); // strip tab prefix (CSV phone fix)
        if ($val === '' || $val === '—') continue;

        if (in_array($dbCol, ['subtotal', 'shipping', 'total'])) {
            // Strip peso sign and commas that Excel may add
            $val = preg_replace('/[^\d.]/', '', $val);
            $num = filter_var($val, FILTER_VALIDATE_FLOAT);
            if ($num === false) {
                $errors[] = "Row " . ($idx + 2) . " ($orderRef): '$csvKey' is not a valid number.";
                continue;
            }
            $setParts[] = "`$dbCol` = ?";
            $bindVals[] = $num;
            $bindTypes .= 'd';
        } else {
            $setParts[] = "`$dbCol` = ?";
            $bindVals[] = $val;
            $bindTypes .= 's';
        }
    }

    if (empty($setParts)) { $skipped++; continue; }

    $tables     = ['orders', 'received_records'];
    $rowUpdated = false;

    foreach ($tables as $table) {
        $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if (!$check || mysqli_num_rows($check) === 0) continue;

        // Sort is by created_at which UPDATE never touches — position preserved automatically
        $sql          = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `order_ref` = ?";
        $bindVals[]   = $orderRef;
        $bindTypes   .= 's';

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            $errors[] = "Row " . ($idx + 2) . " ($orderRef): Prepare failed — " . mysqli_error($conn);
            array_pop($bindVals);
            $bindTypes = substr($bindTypes, 0, -1);
            continue;
        }

        mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindVals);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) { $updated++; $rowUpdated = true; }
        mysqli_stmt_close($stmt);

        array_pop($bindVals);
        $bindTypes = substr($bindTypes, 0, -1);

        if ($rowUpdated) break;
    }

    if (!$rowUpdated) $skipped++;
}

echo json_encode([
    'success' => true,
    'updated' => $updated,
    'skipped' => $skipped,
    'errors'  => $errors,
    'message' => "$updated record(s) updated" . ($skipped ? ", $skipped skipped" : '') . '.',
]);