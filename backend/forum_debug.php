<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "=== USERS TABLE COLUMNS ===\n";
$r = $conn->query("DESCRIBE users");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "ERROR: " . $conn->error . "\n";
}