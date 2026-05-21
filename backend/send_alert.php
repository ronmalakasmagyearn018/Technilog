<?php
// ============================================================
//  send_alert.php  — Technilog/backend/
// ============================================================
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST only.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$user_id      = intval($body['user_id']      ?? 0);
$user_email   = trim($body['user_email']     ?? '');
$user_name    = trim($body['user_name']      ?? 'User');
$alert_type   = trim($body['alert_type']     ?? '');
$model_no     = trim($body['model_no']       ?? '');
$device_model = trim($body['device_model']   ?? '');
$category     = trim($body['category']       ?? '');
$order_ref    = trim($body['order_ref']      ?? '');
$sensor_data  = $body['sensor_data']         ?? [];

if (!$user_id || !$user_email || !in_array($alert_type, ['fire', 'burglar']) || !$model_no) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields.']);
    exit;
}

date_default_timezone_set('Asia/Manila');
$now = date('F j, Y \a\t h:i A');

function sensorRow(string $label, string $value, string $valueColor = '#fff'): string {
    return "<tr>
              <td style=\"color:#888;font-size:13px;padding:5px 0;\">{$label}</td>
              <td style=\"color:{$valueColor};font-size:13px;font-weight:bold;text-align:right;\">{$value}</td>
            </tr>";
}

if ($alert_type === 'fire') {
    $subject    = '🔥 ALERT: Fire Detected — ' . $device_model;
    $alertColor = '#dc2626';
    $alertBg    = '#fff1f2';
    $alertIcon  = '🔥';
    $alertTitle = 'FIRE ALERT TRIGGERED';
    $alertDesc  = 'Smoke or fire has been detected by your device. Please evacuate immediately and contact emergency services if needed.';
    $action     = 'alert_fire';
    $note       = 'TEST: Fire alert triggered by user via Test Alert button.';
    $tipHtml    = '<p style="color:#7f1d1d;font-size:13px;margin:0 0 8px;"><strong>What to do:</strong></p>
      <ul style="color:#7f1d1d;font-size:13px;margin:0;padding-left:18px;line-height:1.8;">
        <li>Evacuate all occupants immediately</li>
        <li>Call <strong>911</strong> or your local fire department</li>
        <li>Do not re-enter the building until cleared</li>
        <li>Use the nearest fire exit</li>
      </ul>';

    $temp    = htmlspecialchars($sensor_data['temperature'] ?? '72.4 °C  ⚠️ DANGER');
    $smoke   = htmlspecialchars($sensor_data['smoke']       ?? 'HIGH — Smoke Detected');
    $co2     = htmlspecialchars($sensor_data['co2']         ?? '1847 ppm  ⚠️ CRITICAL');
    $hum     = htmlspecialchars($sensor_data['humidity']    ?? '84.2 %');
    $extraRows = sensorRow('Temperature', $temp, '#f87171')
               . sensorRow('Smoke Level', $smoke, '#fbbf24')
               . sensorRow('CO₂ Level',   $co2,  '#fbbf24')
               . sensorRow('Humidity',    $hum,  '#fff');
} else {
    $subject    = '🚨 ALERT: Burglar Detected — ' . $device_model;
    $alertColor = '#d97706';
    $alertBg    = '#fffbeb';
    $alertIcon  = '🚨';
    $alertTitle = 'BURGLAR ALERT TRIGGERED';
    $alertDesc  = 'Unauthorized movement has been detected by your CCTV camera. Please check your premises and contact authorities if necessary.';
    $action     = 'alert_burglar';
    $note       = 'TEST: Burglar alert triggered by user via Test Alert button.';
    $tipHtml    = '<p style="color:#78350f;font-size:13px;margin:0 0 8px;"><strong>What to do:</strong></p>
      <ul style="color:#78350f;font-size:13px;margin:0;padding-left:18px;line-height:1.8;">
        <li>Do <strong>NOT</strong> confront the intruder</li>
        <li>Call <strong>911</strong> or your local police</li>
        <li>Stay in a safe location</li>
        <li>Review your CCTV footage as evidence</li>
      </ul>';

    $zone   = htmlspecialchars($sensor_data['motion_zone']   ?? 'Zone A — Main Entrance');
    $reason = htmlspecialchars($sensor_data['motion_reason'] ?? 'Rapid motion detected');
    $fps    = htmlspecialchars($sensor_data['fps_drop']      ?? '3.2 fps (abnormal)');
    $signal = htmlspecialchars($sensor_data['signal']        ?? '92%');
    $extraRows = sensorRow('Motion Zone',   $zone,   '#fbbf24')
               . sensorRow('Reason',        $reason, '#f87171')
               . sensorRow('Frame Anomaly', $fps,    '#fbbf24')
               . sensorRow('Signal',        $signal, '#fff');
}

$html = <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0">
  <tr><td align="center" style="padding:40px 20px;">
    <table width="520" cellpadding="0" cellspacing="0" style="background:#1a1a2e;border-radius:16px;overflow:hidden;">

      <tr><td align="center" style="padding:32px 32px 20px;">
        <h1 style="color:#fff;font-size:22px;letter-spacing:3px;margin:0 0 4px;">TECHNILOG</h1>
        <p style="color:#aaa;font-size:12px;margin:0;">Smart Security Alert System</p>
      </td></tr>

      <tr><td style="padding:0 32px 24px;">
        <div style="background:{$alertBg};border-radius:12px;padding:20px 24px;border-left:5px solid {$alertColor};">
          <p style="font-size:28px;margin:0 0 8px;">{$alertIcon}</p>
          <h2 style="color:{$alertColor};font-size:18px;font-weight:bold;margin:0 0 8px;letter-spacing:1px;">{$alertTitle}</h2>
          <p style="color:#374151;font-size:14px;margin:0;">{$alertDesc}</p>
        </div>
      </td></tr>

      <tr><td style="padding:0 32px 20px;">
        <div style="background:#252547;border-radius:12px;padding:16px 20px;">
          <p style="color:#aaa;font-size:11px;font-weight:bold;letter-spacing:2px;margin:0 0 12px;text-transform:uppercase;">Device Information</p>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="color:#888;font-size:13px;padding:5px 0;">Device</td>
              <td style="color:#fff;font-size:13px;font-weight:bold;text-align:right;">{$device_model}</td>
            </tr>
            <tr>
              <td style="color:#888;font-size:13px;padding:5px 0;">Model No.</td>
              <td style="color:#fff;font-size:13px;font-weight:bold;text-align:right;">{$model_no}</td>
            </tr>
            <tr>
              <td style="color:#888;font-size:13px;padding:5px 0;">Order Ref</td>
              <td style="color:#fff;font-size:13px;font-weight:bold;text-align:right;">{$order_ref}</td>
            </tr>
            <tr>
              <td style="color:#888;font-size:13px;padding:5px 0;">Time Detected</td>
              <td style="color:#fff;font-size:13px;font-weight:bold;text-align:right;">{$now}</td>
            </tr>
            <tr><td colspan="2" style="padding:8px 0 4px;"><div style="border-top:1px solid #3a3a5c;"></div></td></tr>
            {$extraRows}
          </table>
        </div>
      </td></tr>

      <tr><td style="padding:0 32px 24px;">
        <div style="background:#f9fafb;border-radius:12px;padding:16px 20px;">{$tipHtml}</div>
      </td></tr>

      <tr><td style="padding:0 32px 28px;">
        <p style="color:#6b7280;font-size:11px;text-align:center;margin:0;background:#252547;border-radius:8px;padding:10px 16px;">
          ⚠️ This is a <strong style="color:#fff;">TEST ALERT</strong> triggered manually from your Technilog device panel.<br>
          In a real emergency, this alert fires automatically.
        </p>
      </td></tr>

      <tr><td align="center" style="padding:0 32px 28px;">
        <p style="color:#555;font-size:11px;margin:0;">© 2026 TECHNILOG. All rights reserved.</p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;

$sent = _smtpSend($user_email, $user_name, $subject, $html);

$stmt = $conn->prepare(
    "INSERT INTO device_history (user_id, model_no, device_model, category, order_ref, action, note)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('issssss', $user_id, $model_no, $device_model, $category, $order_ref, $action, $note);
$logged = $stmt->execute();
$history_id = $logged ? (int) $conn->insert_id : null;

echo json_encode([
    'success'    => $sent,
    'emailed'    => $sent,
    'logged'     => (bool) $logged,
    'history_id' => $history_id,
    'message'    => $sent
        ? 'Alert email sent and logged successfully.'
        : 'Alert logged but email delivery failed. Check SMTP config.',
]);