<?php
// admin-login-records.php - View admin login records
session_start();
require_once '../backend/config.php';

// Check if user is admin (you should implement proper admin authentication)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.html');
    exit;
}

// Handle filters
$filter = $_GET['filter'] ?? 'all';
$limit = (int)($_GET['limit'] ?? 50);

// Build query based on filter
$whereClause = '';
switch ($filter) {
    case 'success':
        $whereClause = "WHERE status = 'success'";
        break;
    case 'failed':
        $whereClause = "WHERE status = 'failed'";
        break;
    case 'today':
        $whereClause = "WHERE DATE(login_time) = CURDATE()";
        break;
    case 'week':
        $whereClause = "WHERE login_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $whereClause = "WHERE login_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    default:
        $whereClause = '';
}

// Get login records
$query = "SELECT * FROM admin_logins $whereClause ORDER BY login_time DESC LIMIT ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Get statistics
$statsQuery = "SELECT
    COUNT(*) as total_attempts,
    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_logins,
    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_attempts
FROM admin_logins $whereClause";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Login Records — Technilog</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../stylesheet/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50">
  <div class="min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <img src="../image/logo.png" class="h-10 w-10 rounded-full mr-3" alt="Logo" onerror="this.style.display='none'">
            <div>
              <h1 class="text-xl font-bold text-gray-900">Admin Login Records</h1>
              <p class="text-sm text-gray-500">Monitor administrator access</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <a href="../frontend/admin.html" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
              <i class="fas fa-arrow-left mr-1"></i>Back to Admin Panel
            </a>
          </div>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-sign-in-alt text-blue-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <dt class="text-sm font-medium text-gray-500 truncate">Total Attempts</dt>
              <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_attempts'] ?? 0; ?></dd>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <dt class="text-sm font-medium text-gray-500 truncate">Successful Logins</dt>
              <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['successful_logins'] ?? 0; ?></dd>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times-circle text-red-600"></i>
              </div>
            </div>
            <div class="ml-4">
              <dt class="text-sm font-medium text-gray-500 truncate">Failed Attempts</dt>
              <dd class="text-2xl font-semibold text-gray-900"><?php echo $stats['failed_attempts'] ?? 0; ?></dd>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex flex-wrap items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Login Records</h3>
            <div class="flex space-x-2">
              <select onchange="changeFilter(this.value)" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Records</option>
                <option value="success" <?php echo $filter === 'success' ? 'selected' : ''; ?>>Successful Only</option>
                <option value="failed" <?php echo $filter === 'failed' ? 'selected' : ''; ?>>Failed Only</option>
                <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $filter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                <option value="month" <?php echo $filter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
              </select>
              <select onchange="changeLimit(this.value)" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 records</option>
                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 records</option>
                <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200 records</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Records Table -->
        <div class="overflow-x-auto">
          <?php if (empty($records)): ?>
            <div class="text-center py-12">
              <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
              <p class="text-gray-500">No login records found</p>
            </div>
          <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($records as $record): ?>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <?php echo date('M j, Y g:i A', strtotime($record['login_time'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo htmlspecialchars($record['ip_address']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                      <?php echo htmlspecialchars(substr($record['user_agent'], 0, 50)); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php if ($record['status'] === 'success'): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <i class="fas fa-check-circle mr-1"></i>Success
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          <i class="fas fa-times-circle mr-1"></i>Failed
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php if ($record['status'] === 'failed' && $record['failure_reason']): ?>
                        <?php echo htmlspecialchars($record['failure_reason']); ?>
                      <?php else: ?>
                        <span class="text-gray-400">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    function changeFilter(filter) {
      const url = new URL(window.location);
      url.searchParams.set('filter', filter);
      window.location.href = url.toString();
    }

    function changeLimit(limit) {
      const url = new URL(window.location);
      url.searchParams.set('limit', limit);
      window.location.href = url.toString();
    }
  </script>
</body>
</html>