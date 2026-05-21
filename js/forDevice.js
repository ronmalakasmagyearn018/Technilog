const SMART_CATEGORIES = ['Smart CCTV Camera', 'Smart Fire Alarm'];
const _userId    = parseInt(localStorage.getItem('tl_user_id') || '0');
const _userEmail = localStorage.getItem('tl_user_email') || '';
let allDevices      = [];
let currentCat      = 'all';
let telemetryTimers = {};
const devicePowerState = {}; // modelNo -> true/false
let _currentDevice  = null;

// ── Telemetry data generators ─────────────────────────────────────────────
function randomBetween(min, max, dec = 1) {
  return (Math.random() * (max - min) + min).toFixed(dec);
}
function getCctvTelemetry() {
  const motions = ['None detected', 'Motion in Zone A', 'Motion in Zone B', 'None detected', 'None detected'];
  return {
    resolution: '4K / 8MP',
    fps: Math.floor(Math.random() * 6) + 25 + ' fps',
    nightVision: Math.random() > 0.15 ? 'Active' : 'Standby',
    motion: motions[Math.floor(Math.random() * motions.length)],
    storage: randomBetween(40, 85) + '% used',
    signal: Math.floor(Math.random() * 15) + 85 + '%',
    uptime: Math.floor(Math.random() * 30) + 1 + 'd ' + Math.floor(Math.random() * 23) + 'h',
    recording: Math.random() > 0.05 ? 'Recording' : 'Paused',
  };
}
function getFireAlarmTelemetry() {
  const statuses = ['Normal', 'Normal', 'Normal', 'Normal', 'Alert — Check Device'];
  return {
    temperature: randomBetween(22, 35) + ' °C',
    humidity: randomBetween(40, 75) + ' %',
    smoke: Math.random() > 0.92 ? 'Detected!' : 'Clear',
    co2: randomBetween(350, 700, 0) + ' ppm',
    battery: Math.floor(Math.random() * 30) + 70 + '%',
    signal: Math.floor(Math.random() * 10) + 90 + '%',
    alarm: Math.random() > 0.95 ? '🔴 TRIGGERED' : '🟢 Armed & Ready',
    status: statuses[Math.floor(Math.random() * statuses.length)],
  };
}

function renderTelemetry(isCctv) {
  const el = document.getElementById('telemetryPanel');
  if (!el) return;
  if (_currentDevice && devicePowerState[_currentDevice.modelNo] === false) {
    el.innerHTML = `
      <div class="flex flex-col items-center justify-center py-8 gap-2 text-gray-400">
        <i class="fas fa-power-off text-4xl text-gray-300"></i>
        <p class="text-sm font-semibold">Device is powered off</p>
        <p class="text-xs text-gray-300">Turn on the device to see live readings.</p>
      </div>`;
    return;
  }
  const data = isCctv ? getCctvTelemetry() : getFireAlarmTelemetry();
  if (isCctv) {
    const motionColor = data.motion !== 'None detected' ? 'text-amber-600 font-bold' : 'text-green-600 font-bold';
    const recColor    = data.recording === 'Recording'  ? 'text-red-500 font-bold' : 'text-gray-500';
    el.innerHTML = `<div class="grid grid-cols-2 gap-2">
      ${telRow('fa-video','Recording',`<span class="${recColor}">${data.recording}</span>`)}
      ${telRow('fa-film','Resolution',data.resolution)}
      ${telRow('fa-gauge-high','Frame Rate',data.fps)}
      ${telRow('fa-moon','Night Vision',data.nightVision)}
      ${telRow('fa-person-walking','Motion',`<span class="${motionColor}">${data.motion}</span>`)}
      ${telRow('fa-hard-drive','Storage',data.storage)}
      ${telRow('fa-wifi','Signal',data.signal)}
      ${telRow('fa-clock','Uptime',data.uptime)}
    </div>`;
  } else {
    const smokeColor = data.smoke !== 'Clear' ? 'text-red-600 font-bold animate-pulse' : 'text-green-600 font-bold';
    const alarmColor = data.alarm.includes('TRIGGERED') ? 'text-red-600 font-bold' : 'text-green-600 font-bold';
    const statColor  = data.status !== 'Normal' ? 'text-amber-600 font-bold' : 'text-green-600 font-bold';
    el.innerHTML = `<div class="grid grid-cols-2 gap-2">
      ${telRow('fa-thermometer-half','Temperature',data.temperature)}
      ${telRow('fa-droplet','Humidity',data.humidity)}
      ${telRow('fa-smog','Smoke',`<span class="${smokeColor}">${data.smoke}</span>`)}
      ${telRow('fa-wind','CO₂ Level',data.co2)}
      ${telRow('fa-battery-three-quarters','Battery',data.battery)}
      ${telRow('fa-wifi','Signal',data.signal)}
      ${telRow('fa-bell','Alarm',`<span class="${alarmColor}">${data.alarm}</span>`)}
      ${telRow('fa-circle-info','Status',`<span class="${statColor}">${data.status}</span>`)}
    </div>`;
  }
}

function telRow(icon, label, value) {
  return `<div class="bg-gray-50 rounded-xl p-3 flex flex-col gap-1">
    <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-widest text-gray-400">
      <i class="fas ${icon} text-[9px]" style="color:var(--color-secondary);"></i>${label}
    </div>
    <div class="text-sm font-bold text-gray-900 leading-tight">${value}</div>
  </div>`;
}

// ── Power-off confirmation modal ───────────────────────────────────────────
(function injectPowerConfirmModal() {
  if (document.getElementById('powerConfirmModal')) return;
  const el = document.createElement('div');
  el.id = 'powerConfirmModal';
  el.className = 'hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4';
  el.innerHTML = `
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
      <div class="flex flex-col items-center text-center gap-3">
        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center text-red-500 text-2xl">
          <i class="fas fa-power-off"></i>
        </div>
        <h3 class="text-lg font-extrabold text-gray-900">Turn Off Device?</h3>
        <p class="text-sm text-gray-500 leading-relaxed">
          Turning off this device will stop all monitoring and alerts.
          Are you sure you want to continue?
        </p>
      </div>
      <div class="flex gap-3 mt-6">
        <button id="powerConfirmCancel"
          class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
          Cancel
        </button>
        <button id="powerConfirmYes"
          class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-red-500 hover:bg-red-600 transition shadow">
          Yes, Turn Off
        </button>
      </div>
    </div>`;
  document.body.appendChild(el);
  document.getElementById('powerConfirmCancel').addEventListener('click', function() {
    el.classList.add('hidden');
  });
  el.addEventListener('click', function(e) {
    if (e.target === el) el.classList.add('hidden');
  });
})();

// ── Power toggle ──────────────────────────────────────────────────────────
async function toggleDevicePower() {
  if (!_currentDevice) return;
  const d    = _currentDevice;
  const isOn = devicePowerState[d.modelNo] !== false;

  if (isOn) {
    const modal = document.getElementById('powerConfirmModal');
    if (modal) {
      modal.classList.remove('hidden');
      document.getElementById('powerConfirmYes').onclick = async function() {
        modal.classList.add('hidden');
        await _doPowerToggle(d, true);
      };
    }
    return;
  }

  await _doPowerToggle(d, false);
}

async function _doPowerToggle(d, wasOn) {
  const action = wasOn ? 'turned_off' : 'turned_on';
  devicePowerState[d.modelNo] = !wasOn;

  updatePowerButton(!wasOn);
  updateStatusCard(!wasOn, d.category === 'Smart CCTV Camera');
  renderTelemetry(d.category === 'Smart CCTV Camera');

  try {
    await fetch('../backend/device_history.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id:      _userId,
        model_no:     d.modelNo,
        device_model: d.model,
        category:     d.category,
        order_ref:    d.orderRef,
        action,
        note: `User manually ${action.replace('_', ' ')} the device.`,
      }),
    });
    showToast(`Device ${wasOn ? 'turned off' : 'turned on'} successfully.`);
  } catch(err) {
    console.error(err);
    showToast('Action saved. Sync may be delayed.');
  }
}

function updatePowerButton(isOn) {
  const btn = document.getElementById('powerToggleBtn');
  if (!btn) return;
  if (isOn) {
    btn.className = 'flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold border cursor-pointer transition-all bg-red-50 text-red-600 border-red-200 hover:bg-red-100';
    btn.innerHTML = '<i class="fas fa-power-off"></i> Turn Off Device';
  } else {
    btn.className = 'flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold border cursor-pointer transition-all bg-emerald-50 text-emerald-600 border-emerald-200 hover:bg-emerald-100';
    btn.innerHTML = '<i class="fas fa-power-off"></i> Turn On Device';
  }
}

function updateStatusCard(isOn, isCctv) {
  const card  = document.getElementById('modalStatusCard');
  const icon  = document.getElementById('modalStatusIcon');
  const label = document.getElementById('modalStatusLabel');
  const desc  = document.getElementById('modalStatusDesc');
  const live  = document.getElementById('liveBadge');
  if (!card) return;
  const testBtn = document.getElementById('testAlertBtn');
  if (isOn) {
    card.className = 'rounded-2xl p-4 flex items-center gap-4 bg-gradient-to-br from-emerald-100 to-emerald-200 border border-emerald-300';
    icon.textContent  = isCctv ? '📷' : '🔥';
    label.textContent = 'Active & Operational';
    desc.textContent  = isCctv
      ? 'Your CCTV camera is registered and active. Surveillance is running normally.'
      : 'Your fire alarm is registered and active. Detection system is armed and ready.';
    if (live) live.classList.remove('hidden');
    if (testBtn) {
      testBtn.disabled = false;
      testBtn.classList.remove('opacity-40', 'cursor-not-allowed');
      testBtn.classList.add('cursor-pointer', 'hover:bg-amber-100');
      testBtn.title = '';
    }
  } else {
    card.className = 'rounded-2xl p-4 flex items-center gap-4 bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-300';
    icon.textContent  = '⏻';
    label.textContent = 'Device Powered Off';
    desc.textContent  = 'This device has been manually turned off. Turn it on to resume monitoring.';
    if (live) live.classList.add('hidden');
    if (testBtn) {
      testBtn.disabled = true;
      testBtn.classList.add('opacity-40', 'cursor-not-allowed');
      testBtn.classList.remove('cursor-pointer', 'hover:bg-amber-100');
      testBtn.title = 'Turn on the device to use Test Alert';
    }
  }
}

// ── Test Alert ────────────────────────────────────────────────────────────
function openAlertModal() {
  if (!_currentDevice) return;
  const isCctv = _currentDevice.category === 'Smart CCTV Camera';
  // Update modal content based on device type before showing
  const icon  = document.getElementById('alertModalIcon');
  const title = document.getElementById('alertModalTitle');
  const desc  = document.getElementById('alertModalDesc');
  const btn   = document.getElementById('alertModalConfirmBtn');
  if (isCctv) {
    if (icon)  icon.textContent  = '🚨';
    if (title) title.textContent = 'Burglar Detected';
    if (desc)  desc.textContent  = 'This will simulate an unauthorized intrusion alert from your CCTV camera and send an email notification to your registered address.';
    if (btn)   { btn.className = btn.className.replace(/bg-\S+/g,'').replace(/text-\S+/g,'').replace(/border-\S+/g,'') + ' bg-amber-500 hover:bg-amber-600 text-white border-amber-500'; btn.innerHTML = '<i class="fas fa-person-falling-burst"></i> Send Burglar Alert'; }
  } else {
    if (icon)  icon.textContent  = '🔥';
    if (title) title.textContent = 'Fire Detected';
    if (desc)  desc.textContent  = 'This will simulate a fire/smoke detection alert from your fire alarm and send an email notification to your registered address.';
    if (btn)   { btn.className = btn.className.replace(/bg-\S+/g,'').replace(/text-\S+/g,'').replace(/border-\S+/g,'') + ' bg-red-500 hover:bg-red-600 text-white border-red-500'; btn.innerHTML = '<i class="fas fa-fire"></i> Send Fire Alert'; }
  }
  const modal = document.getElementById('alertTypeModal');
  if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
}

function closeAlertModal() {
  const modal = document.getElementById('alertTypeModal');
  if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

// Generate random danger sensor readings to include in the email
function _getDangerSensorData(isCctv) {
  if (isCctv) {
    const zones   = ['Zone A — Main Entrance', 'Zone B — Back Door', 'Zone C — Garage', 'Zone D — Side Window', 'Zone E — Living Room'];
    const reasons = ['Rapid motion detected', 'Unauthorized movement', 'Multiple figures detected', 'Sudden motion burst'];
    return {
      motion_zone:   zones[Math.floor(Math.random() * zones.length)],
      motion_reason: reasons[Math.floor(Math.random() * reasons.length)],
      fps_drop:      (Math.random() * 10 + 2).toFixed(1) + ' fps (abnormal)',
      signal:        (Math.floor(Math.random() * 10) + 88) + '%',
    };
  } else {
    const tempC = (Math.random() * 20 + 65).toFixed(1); // 65–85°C danger range
    return {
      temperature: tempC + ' °C  ⚠️ DANGER',
      smoke:       'HIGH — Smoke Detected',
      co2:         (Math.floor(Math.random() * 500) + 1500) + ' ppm  ⚠️ CRITICAL',
      humidity:    (Math.random() * 10 + 80).toFixed(1) + ' %',
    };
  }
}

async function sendTestAlert() {
  closeAlertModal();
  if (!_currentDevice) return;
  const d      = _currentDevice;
  const isCctv = d.category === 'Smart CCTV Camera';
  const alertType = isCctv ? 'burglar' : 'fire';

  // Show loading state on button
  const btn = document.getElementById('testAlertBtn');
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…'; }

  const userEmail  = localStorage.getItem('tl_user_email') || '';
  const userName   = localStorage.getItem('tl_user_name')  || 'User';
  const sensorData = _getDangerSensorData(isCctv);

  try {
    const res = await fetch('../backend/send_alert.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        user_id:      _userId,
        user_email:   userEmail,
        user_name:    userName,
        alert_type:   alertType,
        model_no:     d.modelNo,
        device_model: d.model,
        category:     d.category,
        order_ref:    d.orderRef,
        sensor_data:  sensorData,
      }),
    });
    const data = await res.json();
    if (data.emailed) {
      showToast(isCctv ? '🚨 Burglar alert sent to your email!' : '🔥 Fire alert sent to your email!');
    } else {
      showToast('Alert logged, but email failed. Check SMTP.');
    }

    // ── Badge + slide-in notification ──────────────────────────────
    // Store the new alert ID so badge and auto-open work across pages
    if (data.history_id) {
      const pending = JSON.parse(localStorage.getItem('tl_pending_alerts') || '[]');
      pending.push(String(data.history_id));
      localStorage.setItem('tl_pending_alerts', JSON.stringify(pending));
    }
    // Bump the history badge count in localStorage so main.html picks it up
    const curBadge = parseInt(localStorage.getItem('tl_history_badge') || '0');
    localStorage.setItem('tl_history_badge', String(curBadge + 1));
    // Show slide-in banner and persist it so it survives navigation
    localStorage.setItem('tl_active_banner', JSON.stringify({ isCctv, historyId: data.history_id }));
    showAlertBanner(isCctv, data.history_id);
  } catch(err) {
    console.error(err);
    showToast('Failed to send alert. Try again.');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-bell"></i> Test Alert';
    }
  }
}

// ── Load devices ──────────────────────────────────────────────────────────
async function loadDevices() {
  if (!_userId && !_userEmail) { showEmpty('Please log in to view your devices.', 'fa-lock'); return; }
  try {
    const url = `../backend/get_deliveries.php?user_id=${encodeURIComponent(_userId)}&email=${encodeURIComponent(_userEmail)}`;
    const orders = await fetch(url).then(r => r.json());
    const devices = [];
    for (const order of orders.filter(o => o.status === 'Received')) {
      let items = [];
      try { items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || []); } catch(e) {}
      for (const item of items) {
        const cat = (item.category || '').trim();
        if (SMART_CATEGORIES.includes(cat)) {
          const prefix  = cat === 'Smart CCTV Camera' ? 'CAM' : 'FAL';
          const hash    = (order.id * 31 + (item.name || '').length * 7) % 9000 + 1000;
          const modelNo = `TL-${prefix}-${hash}`;
          devices.push({
            orderId: order.id,
            orderRef: order.order_ref || `TL-${String(order.id).padStart(5,'0')}`,
            modelNo, receivedAt: order.created_at,
            model: item.name || item.model || 'Unknown Model',
            category: cat, image: item.image || item.img || null,
            qty: item.quantity || item.qty || 1,
            price: item.price || item.unit_price || 0,
          });
        }
      }
    }
    allDevices = devices;
    renderStats();
    renderDevices();
  } catch(err) {
    console.error(err);
    showEmpty('Failed to load devices. Please try again.', 'fa-circle-exclamation');
  }
}

// ── Stats bar ─────────────────────────────────────────────────────────────
function renderStats() {
  const total = allDevices.length;
  const cctv  = allDevices.filter(d => d.category === 'Smart CCTV Camera').length;
  const fire  = allDevices.filter(d => d.category === 'Smart Fire Alarm').length;
  document.getElementById('deviceCountLabel').textContent = `${total} device${total !== 1 ? 's' : ''} found`;
  if (!total) return;
  const bar = document.getElementById('statsBar');
  bar.style.display = 'flex';
  bar.innerHTML = `
    <div class="bg-white rounded-xl px-4 py-2 flex items-center gap-2 text-xs font-bold text-gray-700 shadow-sm">
      <i class="fas fa-laptop" style="color:var(--color-secondary);"></i>${total} Total Device${total!==1?'s':''}
    </div>
    ${cctv?`<div class="bg-white rounded-xl px-4 py-2 flex items-center gap-2 text-xs font-bold text-gray-700 shadow-sm">
      <i class="fas fa-camera" style="color:var(--color-secondary);"></i>${cctv} CCTV Camera${cctv!==1?'s':''}
    </div>`:''}
    ${fire?`<div class="bg-white rounded-xl px-4 py-2 flex items-center gap-2 text-xs font-bold text-gray-700 shadow-sm">
      <i class="fas fa-fire" style="color:var(--color-secondary);"></i>${fire} Fire Alarm${fire!==1?'s':''}
    </div>`:''}
  `;
}

// ── Device grid ───────────────────────────────────────────────────────────
function renderDevices() {
  const grid = document.getElementById('deviceGrid');
  const list = currentCat === 'all' ? allDevices : allDevices.filter(d => d.category === currentCat);
  if (!list.length) {
    showEmpty(
      allDevices.length === 0
        ? 'No smart devices found.<br><span class="text-xs">Only <strong>Order Received</strong> items with Smart CCTV Camera or Smart Fire Alarm category appear here.</span>'
        : `No <strong>${currentCat}</strong> devices found.`,
      'fa-laptop'
    );
    return;
  }
  grid.innerHTML = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">${list.map(deviceCard).join('')}</div>`;
}

function deviceCard(d) {
  const isCctv  = d.category === 'Smart CCTV Camera';
  const catIcon = isCctv ? 'fa-camera' : 'fa-fire-flame-curved';
  const isOn    = devicePowerState[d.modelNo] !== false;
  const imgHtml = d.image
    ? `<img src="${escH(d.image)}" alt="${escH(d.model)}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\\'fas ${catIcon} text-5xl opacity-40\\' style=\\'color:var(--color-secondary);\\'></i>'">`
    : `<i class="fas ${catIcon} text-5xl opacity-40" style="color:var(--color-secondary);"></i>`;
  const statusDot = isOn
    ? `<div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0 pulse-dot" style="box-shadow:0 0 0 3px rgba(34,197,94,0.2);"></div><span class="text-xs font-bold text-gray-700">Active &amp; Registered</span>`
    : `<div class="w-2 h-2 rounded-full bg-gray-400 flex-shrink-0"></div><span class="text-xs font-bold text-gray-400">Powered Off</span>`;

  return `
    <div class="bg-white rounded-[18px] shadow-md overflow-hidden cursor-pointer transition-all duration-200 border-2 border-transparent relative device-card-hover ${!isOn?'opacity-70':''}"
         onclick='openModal(${JSON.stringify(JSON.stringify(d))})'>
      <div class="w-full h-44 bg-gradient-to-br from-[#eef1fb] to-[#dde3f7] flex items-center justify-center overflow-hidden relative">
        ${imgHtml}
        <div class="absolute top-2.5 left-2.5 flex items-center gap-1 text-white text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background:var(--color-primary);">
          <i class="fas ${catIcon}"></i>${escH(d.category)}
        </div>
        ${!isOn?`<div class="absolute inset-0 bg-gray-900/30 flex items-center justify-center">
          <span class="bg-gray-800/70 text-white text-xs font-bold px-3 py-1 rounded-full"><i class="fas fa-power-off mr-1"></i>Off</span>
        </div>`:''}
      </div>
      <div class="p-4 pb-10">
        <div class="font-extrabold text-gray-800 text-[15px] leading-snug mb-0.5">${escH(d.model)}</div>
        <div class="text-xs text-gray-400 font-medium mb-2"><span class="font-semibold text-gray-500">Model No.:</span> ${escH(d.modelNo)}</div>
        <div class="flex items-center gap-2">${statusDot}</div>
      </div>
      <i class="fas fa-chevron-right absolute bottom-4 right-4 text-xs opacity-50" style="color:var(--color-secondary);"></i>
    </div>`;
}

function showEmpty(msg, icon = 'fa-laptop') {
  document.getElementById('deviceGrid').innerHTML = `
    <div class="flex flex-col items-center justify-center py-20 text-gray-400">
      <i class="fas ${icon} text-5xl mb-4" style="color:var(--color-secondary);"></i>
      <p class="text-sm text-center">${msg}</p>
    </div>`;
}

// ── Modal ─────────────────────────────────────────────────────────────────
function openModal(jsonStr) {
  const d = JSON.parse(jsonStr);
  _currentDevice = d;
  const isCctv = d.category === 'Smart CCTV Camera';
  const catIcon = isCctv ? 'fa-camera' : 'fa-fire-flame-curved';
  const isOn    = devicePowerState[d.modelNo] !== false;

  const wrap = document.getElementById('modalImgWrap');
  wrap.innerHTML = d.image
    ? `<img src="${escH(d.image)}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\\'fas ${catIcon} text-6xl opacity-40\\' style=\\'color:var(--color-secondary);\\'></i>'">`
    : `<i class="fas ${catIcon} text-6xl opacity-40" style="color:var(--color-secondary);"></i>`;

  document.getElementById('modalCat').innerHTML        = `<i class="fas ${catIcon} mr-1"></i>${escH(d.category)}`;
  document.getElementById('modalTitle').textContent    = d.model;
  document.getElementById('modalModelNo').textContent  = d.modelNo;
  document.getElementById('modalOrderRef').textContent = d.orderRef;
  document.getElementById('modalDate').textContent     = formatDate(d.receivedAt);
  document.getElementById('modalModel').textContent    = d.model;
  document.getElementById('modalCategory').textContent = d.category;
  document.getElementById('modalQty').textContent      = `${d.qty} unit${d.qty > 1 ? 's' : ''}`;
  document.getElementById('modalPrice').textContent    = `₱${parseFloat(d.price).toLocaleString('en-PH', {minimumFractionDigits:2})}`;

  updateStatusCard(isOn, isCctv);
  updatePowerButton(isOn);

  document.getElementById('telemetryLabel').textContent = isCctv ? 'Live Camera Feed & Stats' : 'Live Sensor Readings';
  renderTelemetry(isCctv);
  clearInterval(telemetryTimers.main);
  telemetryTimers.main = setInterval(() => renderTelemetry(isCctv), 8000);

  const modal = document.getElementById('deviceModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  clearInterval(telemetryTimers.main);
  _currentDevice = null;
  const modal = document.getElementById('deviceModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.style.overflow = '';
  renderDevices();
}

function handleOverlayClick(e) {
  if (e.target === document.getElementById('deviceModal')) closeModal();
}

// ── Tabs ──────────────────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.style.background = ''; b.classList.add('bg-white','text-gray-500'); b.classList.remove('text-white');
    });
    btn.classList.remove('bg-white','text-gray-500'); btn.classList.add('text-white');
    btn.style.background = 'var(--color-primary)';
    currentCat = btn.dataset.cat;
    renderDevices();
  });
});

// ── Utilities ─────────────────────────────────────────────────────────────
function escH(str) {
  return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDate(str) {
  if (!str) return '—';
  const d = new Date(str);
  return isNaN(d) ? str : d.toLocaleDateString('en-PH', {year:'numeric',month:'short',day:'numeric'});
}
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.classList.remove('opacity-0','translate-y-20');
  t.classList.add('opacity-100','translate-y-0');
  setTimeout(() => { t.classList.add('opacity-0','translate-y-20'); t.classList.remove('opacity-100','translate-y-0'); }, 3000);
}

// ── Slide-in alert banner (upper right) ──────────────────────────────────
function showAlertBanner(isCctv, historyId) {
  // Remove any existing banner first
  const old = document.getElementById('tl-alert-banner');
  if (old) old.remove();

  const icon   = isCctv ? '🚨' : '🔥';
  const label  = isCctv ? 'Burglar Alert' : 'Fire Alert';
  const color  = isCctv ? '#d97706' : '#dc2626';
  const border = isCctv ? '#fcd34d' : '#fca5a5';
  const dest   = historyId
    ? `deviceHistory.html?alert=${historyId}`
    : 'deviceHistory.html';

  const banner = document.createElement('div');
  banner.id = 'tl-alert-banner';
  banner.style.cssText = `
    position:fixed; top:16px; left:-340px; z-index:9990;
    width:310px; background:#fff;
    border-left:4px solid ${color};
    border-top:1px solid ${border};
    border-bottom:1px solid ${border};
    border-right:1px solid ${border};
    border-radius:0 16px 16px 0;
    box-shadow:4px 4px 24px rgba(0,0,0,.15);
    padding:16px 18px;
    transition:left .45s cubic-bezier(.4,0,.2,1);
    cursor:pointer;
    font-family:inherit;
  `;
  banner.innerHTML = `
    <div style="display:flex;align-items:flex-start;gap:12px;">
      <div style="font-size:26px;line-height:1;">${icon}</div>
      <div style="flex:1;min-width:0;">
        <div style="font-weight:800;font-size:13px;color:${color};letter-spacing:.4px;">${label} Triggered</div>
        <div style="font-size:12px;color:#6b7280;margin-top:3px;line-height:1.45;">
          Alert logged in Device History.<br>
          <span style="color:${color};font-weight:700;">Tap to view details →</span>
        </div>
      </div>
      <button id="tl-alert-banner-close" style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:0 0 0 4px;line-height:1;flex-shrink:0;" title="Dismiss">✕</button>
    </div>
  `;

  document.body.appendChild(banner);

  // Slide in from left
  requestAnimationFrame(() => {
    requestAnimationFrame(() => { banner.style.left = '0px'; });
  });

  // Click banner → go to device history with alert param
  banner.addEventListener('click', (e) => {
    if (e.target.id === 'tl-alert-banner-close') return;
    localStorage.removeItem('tl_active_banner');
    window.location.href = dest;
  });

  // Close button only — no auto-dismiss
  document.getElementById('tl-alert-banner-close').addEventListener('click', (e) => {
    e.stopPropagation();
    localStorage.removeItem('tl_active_banner');
    banner.style.left = '-340px';
    setTimeout(() => banner.remove(), 450);
  });
}

// ── Re-show banner on page load if still active ──────────────────────────
(function() {
  const stored = localStorage.getItem('tl_active_banner');
  if (!stored) return;
  try {
    const { isCctv, historyId } = JSON.parse(stored);
    // Small delay so page renders first
    setTimeout(() => showAlertBanner(isCctv, historyId), 300);
  } catch(_) { localStorage.removeItem('tl_active_banner'); }
})();

loadDevices();