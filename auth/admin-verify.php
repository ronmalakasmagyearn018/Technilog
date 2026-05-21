<?php
// admin-verify.php - Mobile admin code verification
session_start();
require_once '../backend/config.php';

// Check if this is a verification check request
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    $session = $_GET['session'] ?? '';
    $verified = isset($_SESSION['admin_verified_sessions'][$session]) && $_SESSION['admin_verified_sessions'][$session];
    echo json_encode(['verified' => $verified]);
    exit;
}

// Get session token from URL
$sessionToken = $_GET['session'] ?? '';

if (empty($sessionToken)) {
    die('Invalid session');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = $_POST['admin_code'] ?? '';
    $adminCode = 'Ksn234@JSPedTechni)324';

    if ($enteredCode === $adminCode) {
        // Code is correct - record admin login
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $loginTime = date('Y-m-d H:i:s');

        // Insert admin login record
        $stmt = mysqli_prepare($conn, "
            INSERT INTO admin_logins (session_token, ip_address, user_agent, login_time, status)
            VALUES (?, ?, ?, ?, 'success')
        ");
        mysqli_stmt_bind_param($stmt, 'ssss', $sessionToken, $ipAddress, $userAgent, $loginTime);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Mark session as verified
        $_SESSION['admin_verified_sessions'][$sessionToken] = true;

        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Admin access verified successfully!'
        ]);
    } else {
        // Code is incorrect - record failed attempt
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $loginTime = date('Y-m-d H:i:s');

        $stmt = mysqli_prepare($conn, "
            INSERT INTO admin_logins (session_token, ip_address, user_agent, login_time, status, failure_reason)
            VALUES (?, ?, ?, ?, 'failed', 'incorrect_code')
        ");
        mysqli_stmt_bind_param($stmt, 'ssss', $sessionToken, $ipAddress, $userAgent, $loginTime);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid admin code. Access denied.'
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin Verification — Technilog</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../stylesheet/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm mx-auto bg-white rounded-2xl shadow-xl p-6">

      <!-- LOGO -->
      <div class="flex flex-col items-center mb-6">
        <img src="../image/logo.png" class="h-16 w-16 rounded-full mb-3" alt="Logo" onerror="this.style.display='none'">
        <h1 class="text-2xl font-bold text-gray-800">TECHNILOG</h1>
        <p class="text-sm text-gray-500">Admin Verification</p>
      </div>

      <!-- INSTRUCTIONS -->
      <div class="text-center mb-6">
        <div class="text-4xl text-green-500 mb-3">
          <i class="fas fa-mobile-alt"></i>
        </div>
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Enter Admin Code</h2>
        <p class="text-sm text-gray-600">
          Enter the administrator access code to complete verification
        </p>
      </div>

      <!-- FORM -->
      <form id="verifyForm" class="space-y-4">
        <div>
          <input type="password"
                 id="adminCode"
                 name="admin_code"
                 placeholder="Enter admin code"
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                 required>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition duration-200">
          <i class="fas fa-shield-alt mr-2"></i>Verify Access
        </button>
      </form>

      <!-- STATUS MESSAGE -->
      <div id="messageBox" class="hidden mt-4 p-3 rounded-lg text-center text-sm"></div>

      <!-- BACK BUTTON -->
      <div class="text-center mt-6">
        <button onclick="history.back()"
                class="text-gray-500 hover:text-gray-700 text-sm transition">
          <i class="fas fa-arrow-left mr-1"></i>Back
        </button>
      </div>

    </div>
  </div>

  <script>
    document.getElementById('verifyForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const messageBox = document.getElementById('messageBox');

      // Disable button and show loading
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verifying...';

      try {
        const response = await fetch(window.location.href, {
          method: 'POST',
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          messageBox.className = 'mt-4 p-3 rounded-lg text-center text-sm bg-green-100 text-green-800';
          messageBox.textContent = data.message;
          messageBox.classList.remove('hidden');

          // Redirect back to login page after success
          setTimeout(() => {
            window.location.href = 'admin-login.html';
          }, 2000);
        } else {
          messageBox.className = 'mt-4 p-3 rounded-lg text-center text-sm bg-red-100 text-red-800';
          messageBox.textContent = data.message;
          messageBox.classList.remove('hidden');
        }
      } catch (error) {
        messageBox.className = 'mt-4 p-3 rounded-lg text-center text-sm bg-red-100 text-red-800';
        messageBox.textContent = 'Network error. Please try again.';
        messageBox.classList.remove('hidden');
      } finally {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-shield-alt mr-2"></i>Verify Access';
      }
    });
  </script>
</body>
</html>