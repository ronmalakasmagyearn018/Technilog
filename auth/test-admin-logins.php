<?php
// test-admin-logins.php - Test script for admin login database
require_once '../backend/config.php';

echo "<h1>Admin Login Database Test</h1>";

// Test database connection
if (!$conn) {
    die("<p style='color: red;'>Database connection failed: " . mysqli_connect_error() . "</p>");
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Check if table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'admin_logins'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>✗ admin_logins table does not exist. Please run the SQL schema first.</p>";
    echo "<p><a href='admin_login_schema.sql'>View SQL Schema</a></p>";
} else {
    echo "<p style='color: green;'>✓ admin_logins table exists</p>";
}

// Test insert (optional - comment out in production)
$testSession = 'test_' . time();
$testIP = '127.0.0.1';
$testUA = 'Test User Agent';
$testTime = date('Y-m-d H:i:s');

$stmt = mysqli_prepare($conn, "
    INSERT INTO admin_logins (session_token, ip_address, user_agent, login_time, status)
    VALUES (?, ?, ?, ?, 'success')
");
mysqli_stmt_bind_param($stmt, 'ssss', $testSession, $testIP, $testUA, $testTime);

if (mysqli_stmt_execute($stmt)) {
    echo "<p style='color: green;'>✓ Test record inserted successfully</p>";

    // Clean up test record
    mysqli_query($conn, "DELETE FROM admin_logins WHERE session_token = '$testSession'");
    echo "<p style='color: blue;'>✓ Test record cleaned up</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to insert test record: " . mysqli_error($conn) . "</p>";
}

mysqli_stmt_close($stmt);

// Show current records count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM admin_logins");
$row = mysqli_fetch_assoc($result);
echo "<p>Total admin login records: <strong>" . $row['count'] . "</strong></p>";

if ($row['count'] > 0) {
    echo "<h2>Recent Records (Last 5)</h2>";
    $result = mysqli_query($conn, "SELECT * FROM admin_logins ORDER BY login_time DESC LIMIT 5");

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Session</th><th>IP</th><th>Time</th><th>Status</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['session_token'], 0, 20) . "...</td>";
        echo "<td>" . $row['ip_address'] . "</td>";
        echo "<td>" . $row['login_time'] . "</td>";
        echo "<td>" . ($row['status'] == 'success' ? '<span style="color: green;">✓ Success</span>' : '<span style="color: red;">✗ Failed</span>') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='admin-login-records.php'>View Full Admin Login Records</a></p>";
echo "<p><a href='admin-login.html'>Test Admin Login</a></p>";

mysqli_close($conn);
?>