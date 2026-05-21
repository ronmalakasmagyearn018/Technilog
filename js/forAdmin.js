// ════════════════════════════════
//  PANEL NAVIGATION
// ════════════════════════════════
function showPanel(name) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  const link = document.querySelector(`[data-panel="${name}"]`);
  if (link) link.classList.add('active');
  if (name === 'products' || name === 'dashboard') loadProducts();
  if (name === 'users' || name === 'dashboard') loadUsers();
  if (name === 'dashboard') loadOrders();
  if (name === 'deliveries') loadDeliveries();
  if (name === 'contacts') loadContacts();
  if (name === 'receivedRecords') loadReceivedRecords();
  if (name === 'reports') loadReports();
}

document.querySelectorAll('.sidebar-link').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    showPanel(link.dataset.panel);
  });
});

// Dashboard date
const dashDate = document.getElementById('dashDate');
if (dashDate) {
  dashDate.textContent = new Date().toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

// ════════════════════════════════
//  TOAST
// ════════════════════════════════
function toast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = isError ? '#dc2626' : 'var(--color-primary)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

// ════════════════════════════════
//  PESO FORMAT
// ════════════════════════════════
function peso(val) {
  return '₱' + parseFloat(val || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ════════════════════════════════
//  ORDER STATUS BADGE
// ════════════════════════════════
function orderBadge(status) {
  const map = {
    'Paid': 'badge-paid',
    'Pending': 'badge-pending',
    'Processing': 'badge-processing',
    'Shipped': 'badge-shipped',
    'Cancelled': 'badge-cancelled',
  };
  return `<span class="badge ${map[status] || 'badge-pending'}">${status}</span>`;
}

// Global variables for chart
let currentYear = new Date().getFullYear();
let allOrders = [];

function getPrimaryColor(alpha = 1) {
  const cssColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#2E279D';
  if (alpha === 1) return cssColor;
  if (cssColor.startsWith('#')) {
    const hex = cssColor.slice(1).length === 3
      ? cssColor.slice(1).split('').map(ch => ch + ch).join('')
      : cssColor.slice(1);
    const intVal = parseInt(hex, 16);
    const r = (intVal >> 16) & 255;
    const g = (intVal >> 8) & 255;
    const b = intVal & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }
  return cssColor;
}

// ════════════════════════════════
//  LOAD ORDERS (Dashboard)
// ════════════════════════════════
async function loadOrders() {
  const wrap = document.getElementById('recentOrdersTable');
  if (!wrap) return;
  try {
    const res = await fetch('../backend/get_orders.php');
    const response = await res.json();
    const data = response.data || response; // handle wrapped or direct array
    allOrders = data; // store for chart

    // Stats — only count received orders
    const received = data.filter(o => (o.status || '').toLowerCase() === 'received');
    const total = received.reduce((s, o) => s + parseFloat(o.total || 0), 0);
    const now = new Date();
    const monthly = received
      .filter(o => {
        const d = new Date(o.created_at);
        return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
      })
      .reduce((s, o) => s + parseFloat(o.total || 0), 0);
    const pending = data.filter(o => o.status === 'Pending').length;

    const elR = document.getElementById('statRevenue');
    const elM = document.getElementById('statMonthRevenue');
    const elO = document.getElementById('statOrders');
    const elP = document.getElementById('statPendingOrders');
    if (elR) elR.textContent = peso(total);
    if (elM) elM.textContent = peso(monthly);
    if (elO) elO.textContent = data.length;
    if (elP) elP.textContent = pending;

    // Update chart for current year
    updateSalesChart();
    updateMonthlyChart();
    updateMonthlyGoalsChart();
    updateMonthComparisonChart();

    // Only show orders from the last 7 days
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
    const recentData = data.filter(o => new Date(o.created_at) >= sevenDaysAgo);

    if (!recentData.length) {
      wrap.innerHTML = `<div class="text-center text-gray-400 py-10">
        <i class="fas fa-shopping-bag text-3xl mb-3 block" style="color:#e2e8f0;"></i>
        <p class="text-sm">No orders in the last 7 days.</p>
      </div>`;
      return;
    }

    wrap.innerHTML = `<table class="admin-table">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Product</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        ${recentData.slice(0, 10).map(o => `
          <tr>
            <td class="font-semibold text-gray-700">#${o.id}</td>
            <td>${o.customer_name || '—'}</td>
            <td class="text-gray-500 text-xs">${o.customer_email || '—'}</td>
            <td>${o.items?.[0]?.name || '—'}</td>
            <td class="font-semibold" style="color:var(--color-primary);">${peso(o.total)}</td>
            <td>${orderBadge(o.status)}</td>
            <td class="text-gray-500 text-xs">${new Date(o.created_at).toLocaleDateString('en-PH')}</td>
          </tr>`).join('')}
      </tbody>
    </table>`;
  } catch (e) {
    const wrap = document.getElementById('recentOrdersTable');
    if (wrap) wrap.innerHTML = `<p class="text-red-400 text-center py-6 text-sm">Could not load orders.</p>`;
  }
}

// ════════════════════════════════
//  UPDATE SALES CHART
// ════════════════════════════════
function updateSalesChart() {
  const receivedOrders = allOrders.filter(o => (o.status || '').toLowerCase() === 'received');

  // Group by date — last 30 days only so chart is readable
  const dailySales = {};
  const cutoff = new Date();
  cutoff.setDate(cutoff.getDate() - 30);

  receivedOrders.forEach(o => {
    const d = new Date(o.created_at);
    if (d >= cutoff) {
      const dateKey = d.toISOString().split('T')[0]; // YYYY-MM-DD
      dailySales[dateKey] = (dailySales[dateKey] || 0) + parseFloat(o.total || 0);
    }
  });

  const labels = Object.keys(dailySales).sort();
  const values = labels.map(d => dailySales[d]);

  // Friendly labels: "May 13"
  const friendlyLabels = labels.map(d => {
    const dt = new Date(d + 'T00:00:00');
    return dt.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
  });

  const ctx = document.getElementById('dailySalesChart');
  if (!ctx) return;
  if (window.dailyChartInstance) window.dailyChartInstance.destroy();

  const primaryColor = getPrimaryColor();

  if (!labels.length) {
    // Show empty state
    ctx.style.display = 'none';
    const parent = ctx.parentElement;
    if (!parent.querySelector('.chart-empty')) {
      const em = document.createElement('div');
      em.className = 'chart-empty';
      em.style.cssText = 'text-align:center;padding:40px;color:#9ca3af;font-size:.875rem;';
      em.innerHTML = '<i class="fas fa-chart-line" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3;"></i>No sales data yet.';
      parent.appendChild(em);
    }
    return;
  }
  ctx.style.display = '';
  const em = ctx.parentElement.querySelector('.chart-empty');
  if (em) em.remove();

  window.dailyChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: friendlyLabels,
      datasets: [{
        label: 'Daily Sales (₱)',
        data: values,
        borderColor: primaryColor,
        backgroundColor: getPrimaryColor(0.12),
        pointBackgroundColor: primaryColor,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: 0.35,
        fill: true,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, maxTicksLimit: 10 }
        },
        y: {
          beginAtZero: true,
          max: 200000,
          ticks: {
            stepSize: 25000,
            callback: value => '₱' + value.toLocaleString('en-PH')
          },
          grid: { color: 'rgba(0,0,0,0.05)' }
        }
      }
    }
  });
}

// ════════════════════════════════
//  UPDATE MONTHLY CHART
// ════════════════════════════════
function updateMonthlyChart() {
  const monthlySales = Array(12).fill(0);
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  const receivedOrders = allOrders.filter(o => (o.status || '').toLowerCase() === 'received');
  receivedOrders.forEach(o => {
    const d = new Date(o.created_at);
    if (d.getFullYear() === currentYear) {
      monthlySales[d.getMonth()] += parseFloat(o.total || 0);
    }
  });

  const ctx = document.getElementById('monthlyRevenueChart');
  if (!ctx) return;
  if (window.monthlyChartInstance) window.monthlyChartInstance.destroy();

  const primaryColor = getPrimaryColor();
  const primaryFaint = getPrimaryColor(0.15);
  const secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--color-secondary').trim() || '#4D80E4';

  // Build per-bar colors: highlight current month
  const now = new Date();
  const barColors = monthlySales.map((_, i) =>
    (i === now.getMonth() && currentYear === now.getFullYear()) ? secondaryColor : primaryColor
  );

  window.monthlyChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: monthNames,
      datasets: [{
        label: `Revenue ${currentYear} (₱)`,
        data: monthlySales,
        backgroundColor: barColors,
        borderColor: barColors,
        borderWidth: 0,
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } }
        },
        y: {
          beginAtZero: true,
          max: 200000,
          ticks: {
            stepSize: 25000,
            callback: value => '₱' + value.toLocaleString('en-PH')
          },
          grid: { color: 'rgba(0,0,0,0.05)' }
        }
      }
    }
  });

  // Update year display
  const yearEl = document.getElementById('currentYear');
  if (yearEl) yearEl.textContent = currentYear;
}

// ════════════════════════════════
//  YEAR NAVIGATION
// ════════════════════════════════
document.getElementById('prevYear').addEventListener('click', () => {
  currentYear--;
  updateMonthlyChart();
});

document.getElementById('nextYear').addEventListener('click', () => {
  currentYear++;
  updateMonthlyChart();
});

// ════════════════════════════════
//  MONTHLY GOALS CHART
// ════════════════════════════════
let goalMonth = new Date().getMonth();
let goalYear = new Date().getFullYear();

const GOALS = { revenue: 50000, users: 100, orders: 50 };

function updateMonthlyGoalsChart() {
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  const shortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  // Update label
  const labelEl = document.getElementById('goalMonthLabel');
  if (labelEl) labelEl.textContent = `${shortNames[goalMonth]} ${goalYear}`;

  // Filter received orders for this month/year
  const receivedOrders = allOrders.filter(o => {
    const d = new Date(o.created_at);
    return (o.status || '').toLowerCase() === 'received'
      && d.getMonth() === goalMonth
      && d.getFullYear() === goalYear;
  });
  const revenueActual = receivedOrders.reduce((s, o) => s + parseFloat(o.total || 0), 0);
  const ordersActual = receivedOrders.length;

  // Count users registered in this month/year
  const usersActual = (typeof allUsers !== 'undefined' ? allUsers : []).filter(u => {
    const d = new Date(u.created_at);
    return d.getMonth() === goalMonth && d.getFullYear() === goalYear;
  }).length;

  // Clamp to 100% max for display, but show real value in tooltip
  const revenuePercent = Math.min((revenueActual / GOALS.revenue) * 100, 100);
  const usersPercent = Math.min((usersActual / GOALS.users) * 100, 100);
  const ordersPercent = Math.min((ordersActual / GOALS.orders) * 100, 100);

  const primaryColor = getPrimaryColor();
  const primaryFaint = getPrimaryColor(0.15);

  const ctx = document.getElementById('monthlyGoalsChart');
  if (!ctx) return;
  if (window.goalsChartInstance) window.goalsChartInstance.destroy();

  window.goalsChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Revenue', 'Users', 'Orders'],
      datasets: [
        {
          label: 'Achieved',
          data: [revenuePercent, usersPercent, ordersPercent],
          backgroundColor: [primaryColor, '#10b981', '#f59e0b'],
          borderColor: [primaryColor, '#10b981', '#f59e0b'],
          borderWidth: 0,
          borderRadius: 6,
          borderSkipped: false,
        },
        {
          label: 'Remaining',
          data: [
            Math.max(0, 100 - revenuePercent),
            Math.max(0, 100 - usersPercent),
            Math.max(0, 100 - ordersPercent),
          ],
          backgroundColor: ['rgba(46,39,157,0.08)', 'rgba(16,185,129,0.08)', 'rgba(245,158,11,0.08)'],
          borderColor: ['rgba(46,39,157,0.08)', 'rgba(16,185,129,0.08)', 'rgba(245,158,11,0.08)'],
          borderWidth: 0,
          borderRadius: 6,
          borderSkipped: false,
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            title: items => {
              const idx = items[0].dataIndex;
              return ['Revenue Goal', 'Users Goal', 'Orders Goal'][idx];
            },
            label: (item) => {
              if (item.datasetIndex !== 0) return null;
              const idx = item.dataIndex;
              const actuals = [
                `₱${revenueActual.toLocaleString('en-PH', { minimumFractionDigits: 2 })} / ₱${GOALS.revenue.toLocaleString('en-PH')}`,
                `${usersActual} / ${GOALS.users} users`,
                `${ordersActual} / ${GOALS.orders} orders`,
              ];
              const pct = [revenuePercent, usersPercent, ordersPercent][idx];
              return ` ${actuals[idx]}  (${pct.toFixed(1)}%)`;
            }
          }
        }
      },
      scales: {
        x: {
          stacked: true,
          min: 0,
          max: 100,
          grid: { color: 'rgba(0,0,0,0.04)' },
          ticks: {
            font: { size: 10 },
            callback: v => v + '%'
          }
        },
        y: {
          stacked: true,
          grid: { display: false },
          ticks: { font: { size: 12 } }
        }
      }
    }
  });

  // Status note
  const noteEl = document.getElementById('goalStatusNote');
  if (noteEl) {
    const all100 = revenuePercent >= 100 && usersPercent >= 100 && ordersPercent >= 100;
    const any = revenuePercent > 0 || usersPercent > 0 || ordersPercent > 0;
    if (all100) noteEl.textContent = '🎉 All goals achieved for this month!';
    else if (!any) noteEl.textContent = 'No activity recorded for this month yet.';
    else noteEl.textContent = 'Track your monthly targets vs actuals above.';
  }
}

document.getElementById('prevGoalMonth').addEventListener('click', () => {
  goalMonth--;
  if (goalMonth < 0) { goalMonth = 11; goalYear--; }
  updateMonthlyGoalsChart();
});

document.getElementById('nextGoalMonth').addEventListener('click', () => {
  goalMonth++;
  if (goalMonth > 11) { goalMonth = 0; goalYear++; }
  updateMonthlyGoalsChart();
});

// ════════════════════════════════
//  MONTH COMPARISON CHART
// ════════════════════════════════
function updateMonthComparisonChart() {
  const now = new Date();
  const month0 = { m: now.getMonth(), y: now.getFullYear() };
  const d1 = new Date(now.getFullYear(), now.getMonth() - 1, 1);
  const month1 = { m: d1.getMonth(), y: d1.getFullYear() };
  const d2 = new Date(now.getFullYear(), now.getMonth() - 2, 1);
  const month2 = { m: d2.getMonth(), y: d2.getFullYear() };

  const shortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  function revenueFor(mObj) {
    return allOrders
      .filter(o => {
        const d = new Date(o.created_at);
        return (o.status || '').toLowerCase() === 'received'
          && d.getMonth() === mObj.m && d.getFullYear() === mObj.y;
      })
      .reduce((s, o) => s + parseFloat(o.total || 0), 0);
  }

  const rev0 = revenueFor(month0);
  const rev1 = revenueFor(month1);
  const rev2 = revenueFor(month2);

  const labels = [
    `${shortNames[month2.m]} ${month2.y}`,
    `${shortNames[month1.m]} ${month1.y}`,
    `${shortNames[month0.m]} ${month0.y}`,
  ];

  const colors = [
    'rgba(156,163,175,0.75)',   // 2 months ago — gray
    getPrimaryColor(0.6),       // last month — faded primary
    getPrimaryColor(1),         // this month — full primary
  ];

  const ctx = document.getElementById('monthComparisonChart');
  if (!ctx) return;
  if (window.comparisonChartInstance) window.comparisonChartInstance.destroy();

  window.comparisonChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Revenue (₱)',
        data: [rev2, rev1, rev0],
        backgroundColor: colors,
        borderColor: colors,
        borderWidth: 0,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: item => ' ₱' + item.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 12 } } },
        y: {
          beginAtZero: true,
          ticks: { callback: v => '₱' + v.toLocaleString('en-PH'), font: { size: 10 } },
          grid: { color: 'rgba(0,0,0,0.05)' }
        }
      }
    }
  });

  // Legend with diff
  const legendEl = document.getElementById('comparisonLegend');
  if (legendEl) {
    const diff10 = rev0 - rev1;
    const diff21 = rev1 - rev2;
    const arrow = v => v >= 0 ? `<span class="text-emerald-500">▲ ₱${Math.abs(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>` : `<span class="text-red-400">▼ ₱${Math.abs(v).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>`;
    legendEl.innerHTML = `
      <span>${labels[1]} → ${labels[2]}: ${arrow(diff10)}</span>
      <span>${labels[0]} → ${labels[1]}: ${arrow(diff21)}</span>
    `;
  }
}

// ════════════════════════════════
//  IMAGE UPLOAD  (stores File objects + preview URLs)
// ════════════════════════════════
let uploadedImages = [];   // Array of { file: File, preview: string }

const dropZone = document.getElementById('dropZone');
const imgInput = document.getElementById('imgInput');
const previewGrid = document.getElementById('imagePreviewGrid');

dropZone.addEventListener('click', (e) => { e.stopPropagation(); imgInput.click(); });
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault(); dropZone.classList.remove('drag-over');
  handleFiles([...e.dataTransfer.files]);
});
imgInput.addEventListener('change', () => handleFiles([...imgInput.files]));

function handleFiles(files) {
  files.filter(f => f.type.startsWith('image/')).forEach(file => {
    const preview = URL.createObjectURL(file);
    uploadedImages.push({ file, preview });
    renderPreviews();
  });
}

function renderPreviews() {
  previewGrid.innerHTML = '';
  uploadedImages.forEach((item, i) => {
    const thumb = document.createElement('div');
    thumb.className = 'img-thumb';
    thumb.innerHTML = `<img src="${item.preview}"><button class="remove-img" onclick="removeImg(${i})"><i class="fas fa-times"></i></button>`;
    previewGrid.appendChild(thumb);
  });
}

function removeImg(i) {
  URL.revokeObjectURL(uploadedImages[i].preview);
  uploadedImages.splice(i, 1);
  renderPreviews();
}

// ════════════════════════════════
//  PRICE TIERS
// ════════════════════════════════
document.getElementById('addPriceBtn').addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'price-row';
  row.innerHTML = `
    <input class="form-input" type="text" placeholder="Label (e.g. 4MP, 8MP)" style="max-width:180px;">
    <input class="form-input" type="number" placeholder="Price (₱)" min="0" step="0.01">
    <input class="form-input" type="number" placeholder="Stock" min="0">
    <button class="remove-price-btn" onclick="this.parentElement.remove()"><i class="fas fa-trash"></i></button>`;
  document.getElementById('priceTiers').appendChild(row);
});

function getPrices() {
  const rows = document.querySelectorAll('#priceTiers .price-row');
  const prices = [];
  rows.forEach(row => {
    const inputs = row.querySelectorAll('input');
    const label = inputs[0].value.trim() || 'Standard';
    const price = parseFloat(inputs[1].value) || 0;
    const stock = parseInt(inputs[2].value) || 0;
    if (price > 0) prices.push({ label, price, stock });
  });
  return prices;
}

// ════════════════════════════════
//  SAVE PRODUCT
// ════════════════════════════════
function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

document.getElementById('saveProdBtn').addEventListener('click', async () => {
  const name = document.getElementById('prodName').value.trim();
  const desc = document.getElementById('prodDesc').value.trim();
  const category = document.getElementById('prodCategory').value;
  const specs = document.getElementById('prodSpecs').value.trim();
  const status = document.getElementById('prodStatus').value;
  const featured = document.getElementById('prodFeatured').value;
  const prices = getPrices();

  if (!name) { toast('Product name is required.', true); return; }
  if (!desc) { toast('Description is required.', true); return; }
  if (!prices.length) { toast('Add at least one price.', true); return; }

  const btn = document.getElementById('saveProdBtn');
  btn.disabled = true; btn.textContent = 'Saving…';

  try {
    // Convert images to base64 — bypasses all server file upload config issues
    const imageData = await Promise.all(uploadedImages.map(item => fileToBase64(item.file)));

    const res = await fetch('../backend/save_product.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, desc, category, specs, status, featured: parseInt(featured), prices, imageData }),
    });

    const raw = await res.text();
    let data;
    try {
      data = JSON.parse(raw);
    } catch {
      console.error('PHP returned non-JSON:', raw);
      toast('Server error — check console (F12) for details.', true);
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Product';
      return;
    }

    if (data.success) {
      toast('Product saved!');
      document.getElementById('prodName').value = '';
      document.getElementById('prodDesc').value = '';
      document.getElementById('prodSpecs').value = '';
      uploadedImages.forEach(item => URL.revokeObjectURL(item.preview));
      uploadedImages = []; renderPreviews();
      document.getElementById('priceTiers').innerHTML = `
        <div class="price-row">
          <input class="form-input" type="text" placeholder="Label" style="max-width:180px;">
          <input class="form-input" type="number" placeholder="Price (₱)" min="0" step="0.01">
          <input class="form-input" type="number" placeholder="Stock" min="0">
        </div>`;
      setTimeout(() => showPanel('products'), 800);
    } else {
      toast(data.message || 'Failed to save.', true);
    }
  } catch (e) {
    console.error('Save product fetch error:', e);
    toast('Cannot reach server. Is your local server running?', true);
  } finally {
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Product';
  }
});

// ════════════════════════════════
//  LOAD & RENDER PRODUCTS
// ════════════════════════════════
async function loadProducts() {
  const grid = document.getElementById('productsGrid');
  if (!grid) return;
  try {
    const res = await fetch('../backend/get_products.php');
    const data = await res.json();

    const el = document.getElementById('statProducts');
    if (el) el.textContent = data.length;

    // Low stock (stock <= 5)
    let lowStock = 0;
    data.forEach(p => { if (p.prices) p.prices.forEach(pr => { if (pr.stock <= 5) lowStock++; }); });
    const elL = document.getElementById('statLowStock');
    if (elL) elL.textContent = lowStock;

    if (!data.length) {
      grid.innerHTML = '<p class="text-gray-400 text-center py-12 col-span-full">No products yet.</p>';
      return;
    }
    grid.innerHTML = data.map(p => {
      const img = p.images?.[0] || '';
      const price = p.prices?.[0]?.price ?? 0;
      return `
      <div class="prod-card">
        ${img
          ? `<img class="prod-card-img" src="${img}" alt="${p.name}">`
          : `<div class="prod-card-img flex items-center justify-center text-gray-300"><i class="fas fa-image text-3xl"></i></div>`}
        <div class="prod-card-body">
          <div class="prod-card-name">${p.name}</div>
          <div class="text-xs text-gray-400 mb-1">${p.category}</div>
          <div class="prod-card-desc">${p.description?.substring(0, 80)}${p.description?.length > 80 ? '…' : ''}</div>
          <div class="prod-card-price">${peso(price)}</div>
          <span class="badge mt-1 ${p.status === 'Available' ? 'badge-verified' : 'badge-pending'}">${p.status}</span>
        </div>
        <div class="prod-card-actions">
          <button class="btn-edit" onclick="openEdit(${p.id})"><i class="fas fa-pen mr-1"></i>Edit</button>
          <button class="btn-delete" onclick="deleteProduct(${p.id})"><i class="fas fa-trash mr-1"></i>Delete</button>
        </div>
      </div>`;
    }).join('');
  } catch (e) {
    grid.innerHTML = '<p class="text-red-400 col-span-full text-center py-8">Could not load products.</p>';
  }
}

// ════════════════════════════════
//  DELETE PRODUCT
// ════════════════════════════════
async function deleteProduct(id) {
  if (!confirm('Delete this product?')) return;
  try {
    const res = await fetch('../backend/delete_product.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) { toast('Product deleted.'); loadProducts(); }
    else toast(data.message || 'Failed.', true);
  } catch (e) { toast('Network error.', true); }
}

// ════════════════════════════════
//  EDIT PRODUCT
// ════════════════════════════════
let editImages = []; // new images selected for edit

async function openEdit(id) {
  const res = await fetch(`../backend/get_product.php?id=${id}`);
  const p = await res.json();

  document.getElementById('editId').value = p.id;
  document.getElementById('editName').value = p.name;
  document.getElementById('editDesc').value = p.description || '';
  document.getElementById('editSpecs').value = p.specifications || '';
  document.getElementById('editStatus').value = p.status;
  document.getElementById('editCategory').value = p.category;
  document.getElementById('editFeatured').value = p.featured ?? 0;

  // Show existing images
  const currentWrap = document.getElementById('editCurrentImages');
  currentWrap.innerHTML = '';
  (p.images || []).forEach(src => {
    const div = document.createElement('div');
    div.className = 'img-thumb';
    div.innerHTML = `<img src="${src}" style="width:72px;height:72px;object-fit:cover;border-radius:10px;border:1.5px solid #e2e8f0;">`;
    currentWrap.appendChild(div);
  });

  // Render existing price variants
  const tiers = document.getElementById('editPriceTiers');
  tiers.innerHTML = '';
  const variants = p.prices?.length ? p.prices : [{ label: 'Standard', price: 0, stock: 0 }];
  variants.forEach(v => addEditPriceRow(v.label, v.price, v.stock));

  // Reset new image state
  editImages = [];
  document.getElementById('editImgPreview').innerHTML = '';
  document.getElementById('editImgInput').value = '';

  const modal = document.getElementById('editModal');
  modal.classList.add('show');
}

function addEditPriceRow(label = '', price = '', stock = '') {
  const tiers = document.getElementById('editPriceTiers');
  const row = document.createElement('div');
  row.className = 'price-row';
  row.innerHTML = `
    <input class="form-input" type="text"   value="${label}" placeholder="Label (e.g. Standard, 2MP)" style="max-width:180px;">
    <input class="form-input" type="number" value="${price}" placeholder="Price (₱)" min="0" step="0.01">
    <input class="form-input" type="number" value="${stock}" placeholder="Stock"     min="0">
    <button class="remove-price-btn" onclick="this.parentElement.remove()" title="Remove"><i class="fas fa-trash"></i></button>`;
  tiers.appendChild(row);
}

document.getElementById('editAddPriceBtn').addEventListener('click', () => addEditPriceRow());

// Edit drop zone listeners
(function () {
  const dz = document.getElementById('editDropZone');
  const input = document.getElementById('editImgInput');
  if (!dz || !input) return;
  dz.addEventListener('click', () => input.click());
  dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--color-primary)'; dz.style.background = '#f0f7ff'; });
  dz.addEventListener('dragleave', () => { dz.style.borderColor = '#cbd5e1'; dz.style.background = ''; });
  dz.addEventListener('drop', e => {
    e.preventDefault(); dz.style.borderColor = '#cbd5e1'; dz.style.background = '';
    handleEditFiles([...e.dataTransfer.files]);
  });
  input.addEventListener('change', () => handleEditFiles([...input.files]));
})();

function handleEditFiles(files) {
  files.filter(f => f.type.startsWith('image/')).forEach(file => {
    editImages.push({ file, preview: URL.createObjectURL(file) });
  });
  renderEditPreviews();
}

function renderEditPreviews() {
  const grid = document.getElementById('editImgPreview');
  if (!grid) return;
  grid.innerHTML = '';
  editImages.forEach((item, i) => {
    const thumb = document.createElement('div');
    thumb.className = 'img-thumb';
    thumb.innerHTML = `<img src="${item.preview}"><button class="remove-img" onclick="removeEditImg(${i})"><i class="fas fa-times"></i></button>`;
    grid.appendChild(thumb);
  });
  // Dim existing images when new ones are selected
  const currentWrap = document.getElementById('editCurrentImages');
  if (currentWrap) currentWrap.style.opacity = editImages.length ? '0.4' : '1';
}

function removeEditImg(i) {
  URL.revokeObjectURL(editImages[i].preview);
  editImages.splice(i, 1);
  renderEditPreviews();
}

function closeModal() {
  const modal = document.getElementById('editModal');
  modal.classList.remove('show');
  editImages.forEach(item => URL.revokeObjectURL(item.preview));
  editImages = [];
}

function getEditPrices() {
  const prices = [];
  document.querySelectorAll('#editPriceTiers .price-row').forEach(row => {
    const inputs = row.querySelectorAll('input');
    const label = inputs[0].value.trim() || 'Standard';
    const price = parseFloat(inputs[1].value) || 0;
    const stock = parseInt(inputs[2].value) || 0;
    if (price > 0) prices.push({ label, price, stock });
  });
  return prices;
}

document.getElementById('saveEditBtn').addEventListener('click', async () => {
  const id = document.getElementById('editId').value;
  const name = document.getElementById('editName').value.trim();
  const desc = document.getElementById('editDesc').value.trim();
  const specs = document.getElementById('editSpecs').value.trim();
  const status = document.getElementById('editStatus').value;
  const category = document.getElementById('editCategory').value;
  const featured = document.getElementById('editFeatured').value;
  let prices = getEditPrices();

  // If status is Out of Stock, zero all stock values
  if (status === 'Out of Stock') {
    prices = prices.map(p => ({ ...p, stock: 0 }));
    document.querySelectorAll('#editPriceTiers .price-row').forEach(row => {
      const inputs = row.querySelectorAll('input');
      if (inputs[2]) inputs[2].value = 0;
    });
  }

  if (!name) { toast('Product name is required.', true); return; }
  if (!desc) { toast('Description is required.', true); return; }
  if (!prices.length) { toast('Add at least one price.', true); return; }

  const btn = document.getElementById('saveEditBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving…';

  const fd = new FormData();
  fd.append('id', id);
  fd.append('name', name);
  fd.append('desc', desc);
  fd.append('specs', specs);
  fd.append('status', status);
  fd.append('category', category);
  fd.append('featured', featured);
  fd.append('prices', JSON.stringify(prices));
  editImages.forEach(item => fd.append('images[]', item.file));

  try {
    const res = await fetch('../backend/update_product.php', { method: 'POST', body: fd });
    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); } catch { toast('Server error — check console.', true); return; }
    if (data.success) {
      toast('Product updated!');
      closeModal();
      loadProducts();
    } else {
      toast(data.message || 'Failed to save.', true);
    }
  } catch (e) {
    toast('Cannot reach server.', true);
  } finally {
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Changes';
  }
});

// ════════════════════════════════
//  LOAD USERS
// ════════════════════════════════
let allUsers = [];

async function loadUsers() {
  try {
    const res = await fetch('../backend/get_users.php');
    allUsers = await res.json();

    // Separate users into categories
    const now = new Date();
    // Use start of day 7 days ago so a user registered exactly 7 days ago is excluded
    const sevenDaysAgo = new Date(now);
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
    sevenDaysAgo.setHours(0, 0, 0, 0);

    const adminUsers = allUsers.filter(u => u.role === 'admin');
    const regularUsers = allUsers.filter(u => u.role !== 'admin');
    // Only users created strictly within the last 7 days are "new/recent"
    const newUsers = regularUsers.filter(u => {
      const d = new Date(u.created_at);
      return !isNaN(d) && d >= sevenDaysAgo;
    });
    const oldUsers = regularUsers.filter(u => {
      const d = new Date(u.created_at);
      return isNaN(d) || d < sevenDaysAgo;
    });

    const verified = allUsers.filter(u => u.is_verified == 1).length;
    const pending = allUsers.filter(u => u.is_verified == 0).length;

    const elU = document.getElementById('statUsers');
    const elV = document.getElementById('statVerified');
    if (elU) elU.textContent = allUsers.length;
    if (elV) elV.textContent = verified;

    const label = document.getElementById('userCountLabel');
    if (label) label.textContent = `${allUsers.length} users — ${verified} verified, ${pending} pending`;

    // Populate new users section
    const newUserEl = document.getElementById('newUserCount');
    if (newUserEl) newUserEl.textContent = newUsers.length;
    renderUsersTable(newUsers, 'newUsersTableWrap');

    // Populate all/older users section
    const oldUserEl = document.getElementById('oldUserCount');
    if (oldUserEl) oldUserEl.textContent = oldUsers.length;
    renderUsersTable(oldUsers, 'usersTableWrap');

    // Populate admins section
    const adminUserEl = document.getElementById('adminUserCount');
    if (adminUserEl) adminUserEl.textContent = adminUsers.length;
    renderAdminUsers(adminUsers);

    // Recent users in dashboard (only regular users registered in the last 7 days)
    // Re-filter to be safe — users older than 7 days are not shown
    const sevenDaysAgoCheck = new Date();
    sevenDaysAgoCheck.setDate(sevenDaysAgoCheck.getDate() - 7);
    sevenDaysAgoCheck.setHours(0, 0, 0, 0);
    const trulyRecent = regularUsers.filter(u => {
      const d = new Date(u.created_at);
      return !isNaN(d) && d >= sevenDaysAgoCheck;
    });
    renderRecentUsers(trulyRecent.slice(0, 5));
    // Refresh goals chart so user count for the month is accurate
    if (typeof updateMonthlyGoalsChart === 'function') updateMonthlyGoalsChart();
  } catch (e) { console.log('Users load error', e); }
}

function renderUsersTable(users, wrapperId = 'usersTableWrap') {
  const wrap = document.getElementById(wrapperId);
  if (!wrap) return;
  if (!users.length) {
    wrap.innerHTML = '<p class="text-center text-gray-400 py-10 text-sm">No users found.</p>';
    return;
  }
  wrap.innerHTML = `<table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>User</th>
        <th>Email</th>
        <th>Verified</th>
        <th>Role</th>
        <th>Banned</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      ${users.map((u, i) => {
    const initials = (u.username || 'U').slice(0, 2).toUpperCase();
    const isVerified = u.is_verified == 1;
    const isBanned = u.is_banned == 1;
    const isAdmin = u.role === 'admin';
    return `
        <tr>
          <td class="text-gray-400 text-xs">${i + 1}</td>
          <td>
            <div class="flex items-center gap-2">
              <div class="user-avatar" style="${isAdmin ? 'background:#7c3aed;' : u.auth_provider === 'google' ? 'background:#ea4335;' : ''}">${initials}</div>
              <div>
                <div class="font-semibold text-gray-700">${u.username}</div>
                ${isAdmin ? '<div class="text-xs text-purple-600 font-semibold">Admin</div>' : ''}
                ${u.auth_provider === 'google' ? '<div class="text-xs font-semibold flex items-center gap-1" style="color:#ea4335;"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" style="width:11px;height:11px;"> Google</div>' : ''}
              </div>
            </div>
          </td>
          <td class="text-gray-500 text-sm">${u.email}</td>
          <td>
            <select onchange="handleUserAction(${u.id}, this.value, this)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none"
              style="min-width:105px;">
              <option value="verify"   ${isVerified ? 'selected' : ''}>✅ Verified</option>
              <option value="unverify" ${!isVerified ? 'selected' : ''}>⏳ Pending</option>
            </select>
          </td>
          <td>
            <select onchange="handleUserAction(${u.id}, this.value, this)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none"
              style="min-width:115px;">
              <option value="make_admin"   ${isAdmin ? 'selected' : ''}>🛡️ Admin</option>
              <option value="remove_admin" ${!isAdmin ? 'selected' : ''}>👤 User</option>
            </select>
          </td>
          <td>
            <select onchange="handleUserAction(${u.id}, this.value, this)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none"
              style="min-width:115px;">
              <option value="unban" ${!isBanned ? 'selected' : ''}>🟢 Active</option>
              <option value="ban"   ${isBanned ? 'selected' : ''}>🔴 Banned</option>
            </select>
          </td>
          <td class="text-gray-500 text-xs">${new Date(u.created_at).toLocaleDateString('en-PH')}</td>
          <td>
            <button onclick="openUserModal(${i})" class="text-xs px-3 py-1 rounded-lg font-semibold" style="background:#f0f7ff;color:var(--color-primary);">
              <i class="fas fa-eye mr-1"></i>View
            </button>
          </td>
        </tr>`;
  }).join('')}
    </tbody>
  </table>`;
}

function renderAdminUsers(users) {
  const wrap = document.getElementById('adminUsersTableWrap');
  if (!wrap) return;
  if (!users.length) {
    wrap.innerHTML = '<p class="text-center text-gray-400 py-10 text-sm">No admins found.</p>';
    return;
  }
  wrap.innerHTML = `<table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Admin</th>
        <th>Email</th>
        <th>Verified</th>
        <th>Remove Admin</th>
        <th>Joined</th>
      </tr>
    </thead>
    <tbody>
      ${users.map((u, i) => {
    const initials = (u.username || 'U').slice(0, 2).toUpperCase();
    const isVerified = u.is_verified == 1;
    return `
        <tr>
          <td class="text-gray-400 text-xs">${i + 1}</td>
          <td>
            <div class="flex items-center gap-2">
              <div class="user-avatar" style="background:#7c3aed;">${initials}</div>
              <div>
                <div class="font-semibold text-gray-700">${u.username}</div>
                <div class="text-xs text-purple-600 font-semibold">🛡️ Administrator</div>
                ${u.auth_provider === 'google' ? '<div class="text-xs font-semibold flex items-center gap-1" style="color:#ea4335;"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" style="width:11px;height:11px;"> Google</div>' : ''}
              </div>
            </div>
          </td>
          <td class="text-gray-500 text-sm">${u.email}</td>
          <td>
            <select onchange="handleUserAction(${u.id}, this.value, this)"
              class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none"
              style="min-width:105px;">
              <option value="verify"   ${isVerified ? 'selected' : ''}>✅ Verified</option>
              <option value="unverify" ${!isVerified ? 'selected' : ''}>⏳ Pending</option>
            </select>
          </td>
          <td>
            <button onclick="handleUserAction(${u.id}, 'remove_admin', this)" class="text-xs px-3 py-1 rounded-lg font-semibold" style="background:#fee2e2;color:#dc2626;">
              <i class="fas fa-user-minus mr-1"></i>Remove Admin
            </button>
          </td>
          <td class="text-gray-500 text-xs">${new Date(u.created_at).toLocaleDateString('en-PH')}</td>
        </tr>`;
  }).join('')}
    </tbody>
  </table>`;
}

// ════════════════════════════════
//  HANDLE USER ACTIONS (combobox)
// ════════════════════════════════
async function handleUserAction(userId, action, selectEl) {
  selectEl.disabled = true;
  try {
    const res = await fetch('../backend/update_user.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: userId, action }),
    });
    const data = await res.json();
    if (data.success) {
      const labels = {
        verify: 'User verified!', unverify: 'User set to pending.',
        ban: 'User banned.', unban: 'User unbanned.',
        make_admin: 'User promoted to Admin!', remove_admin: 'Admin role removed.',
      };
      toast(labels[action] || 'Updated.');
      await loadUsers(); // re-render so badges refresh
    } else {
      toast(data.message || 'Failed.', true);
      selectEl.disabled = false;
    }
  } catch (e) {
    toast('Cannot reach server.', true);
    selectEl.disabled = false;
  }
}

function renderRecentUsers(users) {
  const recent = document.getElementById('recentUsersTable');
  if (!recent) return;
  if (!users.length) { recent.innerHTML = '<p class="text-center text-gray-400 text-sm py-6">No new users in the last 7 days.</p>'; return; }
  recent.innerHTML = `<table class="admin-table">
    <thead><tr><th>User</th><th>Email</th><th>Status</th><th>Joined</th></tr></thead>
    <tbody>
      ${users.map(u => {
    const initials = (u.username || 'U').slice(0, 2).toUpperCase();
    return `<tr>
          <td>
            <div class="flex items-center gap-2">
              <div class="user-avatar" style="${u.auth_provider === 'google' ? 'background:#ea4335;' : ''}">${initials}</div>
              <div>
                <span class="font-semibold text-gray-700">${u.username}</span>
                ${u.auth_provider === 'google' ? '<div class="text-xs font-semibold flex items-center gap-1" style="color:#ea4335;"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" style="width:11px;height:11px;"> Google</div>' : ''}
              </div>
            </div>
          </td>
          <td class="text-gray-500 text-sm">${u.email}</td>
          <td><span class="badge ${u.is_verified == 1 ? 'badge-verified' : 'badge-pending'}">${u.is_verified == 1 ? 'Verified' : 'Pending'}</span></td>
          <td class="text-gray-500 text-xs">${new Date(u.created_at).toLocaleDateString('en-PH')}</td>
        </tr>`;
  }).join('')}
    </tbody>
  </table>`;
}

// ════════════════════════════════
//  FILTER USERS
// ════════════════════════════════
function filterUsers() {
  const q = document.getElementById('userSearch')?.value.toLowerCase() || '';
  const status = document.getElementById('userStatusFilter')?.value;
  let filtered = allUsers.filter(u => {
    const matchQ = u.username.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
    if (!matchQ) return false;
    if (status === 'verified') return u.is_verified == 1;
    if (status === 'pending') return u.is_verified == 0;
    if (status === 'admin') return u.role === 'admin';
    if (status === 'banned') return u.is_banned == 1;
    if (status === 'google') return u.auth_provider === 'google';
    return true;
  });

  // Render based on whether admin filter is active
  if (status === 'admin') {
    renderAdminUsers(filtered.filter(u => u.role === 'admin'));
  } else {
    // For regular user filtering, show in the all users table
    renderUsersTable(filtered, 'usersTableWrap');
  }
}

// ════════════════════════════════
//  USER MODAL
// ════════════════════════════════
function openUserModal(index) {
  const u = allUsers[index];
  const body = document.getElementById('viewUserBody');
  const initials = (u.username || 'U').slice(0, 2).toUpperCase();
  const isAdmin = u.role === 'admin';
  const isBanned = u.is_banned == 1;
  const isVerified = u.is_verified == 1;
  body.innerHTML = `
    <div class="flex items-center gap-4 mb-5">
      <div class="user-avatar" style="width:56px;height:56px;font-size:1.2rem;${isAdmin ? 'background:#7c3aed;' : ''}">${initials}</div>
      <div>
        <div class="font-bold text-gray-800 text-lg">${u.username}</div>
        <div class="text-sm text-gray-500">${u.email}</div>
      </div>
    </div>
    <div class="space-y-0 text-sm mb-5">
      <div class="flex justify-between py-2 border-b border-gray-100">
        <span class="text-gray-500 font-medium">Verified</span>
        <span class="badge ${isVerified ? 'badge-verified' : 'badge-pending'}">${isVerified ? 'Verified' : 'Pending'}</span>
      </div>
      <div class="flex justify-between py-2 border-b border-gray-100">
        <span class="text-gray-500 font-medium">Role</span>
        <span class="badge ${isAdmin ? 'badge-processing' : 'badge-pending'}">${isAdmin ? '🛡️ Admin' : '👤 User'}</span>
      </div>
      <div class="flex justify-between py-2 border-b border-gray-100">
        <span class="text-gray-500 font-medium">Account</span>
        <span class="badge ${isBanned ? 'badge-cancelled' : 'badge-verified'}">${isBanned ? '🔴 Banned' : '🟢 Active'}</span>
      </div>
      <div class="flex justify-between py-2 border-b border-gray-100">
        <span class="text-gray-500 font-medium">Joined</span>
        <span class="text-gray-700">${new Date(u.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
      </div>
      <div class="flex justify-between py-2 border-b border-gray-100">
        <span class="text-gray-500 font-medium">Sign-in Method</span>
        ${u.auth_provider === 'google'
      ? '<span class="flex items-center gap-1 text-sm font-semibold" style="color:#ea4335;"><img src=\"https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg\" style=\"width:14px;height:14px;\"> Google</span>'
      : '<span class="text-gray-700 text-sm">🔒 Email / Password</span>'}
      </div>
      <div class="flex justify-between py-2">
        <span class="text-gray-500 font-medium">User ID</span>
        <span class="text-gray-700">#${u.id}</span>
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      <button onclick="quickAction(${u.id},'${isVerified ? 'unverify' : 'verify'}')" 
        class="flex-1 text-xs py-2 px-3 rounded-lg font-semibold transition"
        style="background:${isVerified ? '#fef3c7' : '#dcfce7'};color:${isVerified ? '#b45309' : '#16a34a'};">
        ${isVerified ? '⏳ Set Pending' : '✅ Verify'}
      </button>
      <button onclick="quickAction(${u.id},'${isBanned ? 'unban' : 'ban'}')"
        class="flex-1 text-xs py-2 px-3 rounded-lg font-semibold transition"
        style="background:${isBanned ? '#dcfce7' : '#fee2e2'};color:${isBanned ? '#16a34a' : '#dc2626'};">
        ${isBanned ? '🟢 Unban' : '🔴 Ban User'}
      </button>
      <button onclick="quickAction(${u.id},'${isAdmin ? 'remove_admin' : 'make_admin'}')"
        class="flex-1 text-xs py-2 px-3 rounded-lg font-semibold transition"
        style="background:${isAdmin ? '#f3f4f6' : '#ede9fe'};color:${isAdmin ? '#374151' : '#7c3aed'};">
        ${isAdmin ? '👤 Remove Admin' : '🛡️ Make Admin'}
      </button>
    </div>`;
  const modal = document.getElementById('viewUserModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
}

async function quickAction(userId, action) {
  try {
    const res = await fetch('../backend/update_user.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: userId, action }),
    });
    const data = await res.json();
    const labels = {
      verify: 'User verified!', unverify: 'Set to Pending.',
      ban: 'User banned.', unban: 'User unbanned.',
      make_admin: 'Promoted to Admin!', remove_admin: 'Admin role removed.',
    };
    if (data.success) {
      toast(labels[action] || 'Updated.');
      closeUserModal();
      await loadUsers();
    } else {
      toast(data.message || 'Failed.', true);
    }
  } catch (e) {
    toast('Cannot reach server.', true);
  }
}

function closeUserModal() {
  const modal = document.getElementById('viewUserModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
}

// ════════════════════════════════
//  INIT
// ════════════════════════════════
loadProducts();
loadUsers();
loadOrders();

// Auto-refresh orders every 30 seconds on dashboard
let orderRefreshInterval;
function startOrderRefresh() {
  if (orderRefreshInterval) clearInterval(orderRefreshInterval);
  orderRefreshInterval = setInterval(() => {
    if (document.getElementById('panel-dashboard').classList.contains('active')) {
      loadOrders();
    }
  }, 30000);
}
function stopOrderRefresh() {
  if (orderRefreshInterval) {
    clearInterval(orderRefreshInterval);
    orderRefreshInterval = null;
  }
}

// Start refresh when showing dashboard
document.querySelectorAll('.sidebar-link').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    showPanel(link.dataset.panel);
    if (link.dataset.panel === 'dashboard') {
      startOrderRefresh();
    } else {
      stopOrderRefresh();
    }
  });
});

// Start on load if dashboard is active
if (document.getElementById('panel-dashboard').classList.contains('active')) {
  startOrderRefresh();
}
let allDeliveries = [];

async function loadDeliveries() {
  const wrap = document.getElementById('deliveriesTableBody');
  if (!wrap) return;
  wrap.innerHTML = `<tr><td colspan="16" class="text-center py-8 text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>`;
  try {
    const res = await fetch('../backend/get_deliveries.php');
    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); }
    catch {
      console.error('get_deliveries returned:', raw);
      wrap.innerHTML = `<tr><td colspan="16" class="text-center py-6 text-red-400">Server error — check console (F12).</td></tr>`;
      return;
    }
    if (data.error) {
      wrap.innerHTML = `<tr><td colspan="16" class="text-center py-6 text-red-400">DB Error: ${data.error}</td></tr>`;
      return;
    }
    allDeliveries = (Array.isArray(data) ? data : []).filter(o => o.status !== 'Received');
    renderDeliveries(allDeliveries);
    // Clear badge — admin is now viewing Deliveries
    updateDeliveriesBadge(0);
  } catch (e) {
    wrap.innerHTML = `<tr><td colspan="16" class="text-center py-6 text-red-400">Cannot reach server.</td></tr>`;
  }
}

// ── Deliveries notification badge ──────────────────────────────
function updateDeliveriesBadge(count) {
  const badge = document.getElementById('deliveriesNotifBadge');
  if (!badge) return;
  if (count > 0) {
    badge.textContent = count > 99 ? '99+' : count;
    badge.classList.remove('hidden');
  } else {
    badge.classList.add('hidden');
  }
}

// Poll for new Pending orders in the background every 30 seconds
let _deliveriesBadgeInterval = null;
async function pollDeliveriesBadge() {
  try {
    const res = await fetch('../backend/get_deliveries.php');
    const raw = await res.text();
    const data = JSON.parse(raw);
    if (Array.isArray(data)) {
      // Only count if the Deliveries panel is NOT currently open
      const panel = document.getElementById('panel-deliveries');
      if (panel && panel.classList.contains('active')) {
        updateDeliveriesBadge(0);
      } else {
        const pendingCount = data.filter(o => o.status === 'Pending').length;
        updateDeliveriesBadge(pendingCount);
      }
    }
  } catch (_) { }
}

// Start polling immediately and every 30s
pollDeliveriesBadge();
_deliveriesBadgeInterval = setInterval(pollDeliveriesBadge, 30000);

function renderDeliveries(list) {
  const wrap = document.getElementById('deliveriesTableBody');
  if (!wrap) return;
  if (!list.length) {
    wrap.innerHTML = `<tr><td colspan="17" class="text-center py-10 text-gray-400"><i class="fas fa-inbox text-2xl block mb-2"></i>No orders yet.</td></tr>`;
    return;
  }
  const sc = {
    Pending: 'bg-yellow-100 text-yellow-700', Processing: 'bg-blue-100 text-blue-700',
    Shipped: 'bg-purple-100 text-purple-700', Delivered: 'bg-green-100 text-green-700',
    Received: 'bg-emerald-100 text-emerald-700', Cancelled: 'bg-red-100 text-red-700',
  };
  wrap.innerHTML = list.map(o => {
    let items = [];
    try { items = JSON.parse(o.items || '[]'); } catch { }
    const itemStr = items.map(i => `${i.name} x${i.qty || 1}`).join(', ') || '—';
    const color = sc[o.status] || 'bg-gray-100 text-gray-600';
    const isFinal = o.status === 'Delivered' || o.status === 'Received' || o.status === 'Cancelled';
    return `<tr class="hover:bg-gray-50 border-b border-gray-100 text-sm">
      <td class="px-3 py-3 font-medium text-gray-700">#${o.id}</td>
      <td class="px-3 py-3">${o.order_ref || '—'}</td>
      <td class="px-3 py-3">${o.customer_name || '—'}</td>
      <td class="px-3 py-3 text-gray-500">${o.customer_email || '—'}</td>
      <td class="px-3 py-3 text-gray-500">${o.customer_phone || '—'}</td>
      <td class="px-3 py-3 text-gray-500" style="min-width:220px;white-space:normal;word-break:break-word;">${o.address || '—'}</td>
      <td class="px-3 py-3 text-gray-500 max-w-[100px] truncate tl-tip">${o.notes || '—'}<span class="tl-tip-box">${o.notes || '—'}</span></td>
      <td class="px-3 py-3">${o.payment_method || '—'}</td>
      <td class="px-3 py-3">${o.service || '—'}</td>
      <td class="px-3 py-3 text-right">₱${parseFloat(o.installation_fee || 0).toFixed(2)}</td>
      <td class="px-3 py-3 max-w-[150px] truncate text-gray-500 tl-tip">${itemStr}<span class="tl-tip-box">${itemStr}</span></td>
      <td class="px-3 py-3 text-right">₱${parseFloat(o.subtotal || 0).toFixed(2)}</td>
      <td class="px-3 py-3 text-right">₱${parseFloat(o.shipping || 0).toFixed(2)}</td>
      <td class="px-3 py-3 text-right font-semibold">₱${parseFloat(o.total || 0).toFixed(2)}</td>
      <td class="px-3 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold ${color}">${o.status}</span></td>
      <td class="px-3 py-3 flex items-center gap-2">
        ${!isFinal ? `
        <select onchange="updateDeliveryStatus(${o.id},this.value,this)"
          class="text-xs border border-gray-300 rounded-lg px-2 py-1 bg-white focus:outline-none focus:ring-2 focus:ring-blue-300">
          <option value="Pending"    ${o.status === 'Pending' ? 'selected' : ''}>Pending</option>
          <option value="Processing" ${o.status === 'Processing' ? 'selected' : ''}>Processing</option>
          <option value="Shipped"    ${o.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
          <option value="Delivered"  ${o.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
        </select>
        <button onclick="cancelOrder(${o.id})" 
          class="text-xs px-2 py-1 rounded-lg font-semibold text-white bg-red-500 hover:bg-red-600 active:scale-95 transition">
          Cancel
        </button>` : `<span class="text-xs text-gray-400 italic">${o.status}</span>`}
      </td>
    </tr>`;
  }).join('');
}


async function updateDeliveryStatus(orderId, newStatus, sel) {
  sel.disabled = true;
  try {
    const res = await fetch('../backend/update_delivery.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: orderId, status: newStatus }),
    });
    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); }
    catch { toast('Server error.', true); sel.disabled = false; return; }
    if (data.success) { toast('Status updated to ' + newStatus + '!'); loadDeliveries(); }
    else { toast(data.message || 'Failed.', true); sel.disabled = false; }
  } catch (e) { toast('Cannot reach server.', true); sel.disabled = false; }
}

async function cancelOrder(orderId) {
  if (!confirm('Cancel this order and restore stock?')) return;

  try {
    const res = await fetch('../backend/cancel_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id: orderId })
    });
    const data = await res.json();
    if (data.success) {
      toast('Order cancelled and stock restored!');
      loadDeliveries();
    } else {
      toast(data.message || 'Failed to cancel order.', true);
    }
  } catch (e) {
    toast('Cannot reach server.', true);
  }
}

// Search
const deliverySearch = document.getElementById('deliverySearch');
if (deliverySearch) {
  deliverySearch.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    renderDeliveries(!q ? allDeliveries : allDeliveries.filter(o =>
      (o.order_ref || '').toLowerCase().includes(q) ||
      (o.customer_name || '').toLowerCase().includes(q) ||
      (o.customer_email || '').toLowerCase().includes(q) ||
      (o.customer_phone || '').toLowerCase().includes(q) ||
      (o.status || '').toLowerCase().includes(q)
    ));
  });
}
// ════════════════════════════════
//  CONTACTS
// ════════════════════════════════
let allContacts = [];
let currentReplyId = null;

const SEVEN_DAYS_MS = 7 * 24 * 60 * 60 * 1000;

function isRecentMessage(dateStr) {
  if (!dateStr) return false;
  const ms = new Date(String(dateStr).replace(' ', 'T')).getTime();
  if (isNaN(ms)) return false;
  return (Date.now() - ms) <= SEVEN_DAYS_MS;
}

async function loadContacts() {
  const newWrap = document.getElementById('newContactsTableWrap');
  const oldWrap = document.getElementById('contactsTableWrap');
  const spinner = `<div class="text-center py-12 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i><p class="text-sm">Loading messages…</p></div>`;
  if (newWrap) newWrap.innerHTML = spinner;
  if (oldWrap) oldWrap.innerHTML = '';

  try {
    const res = await fetch('../backend/get_contacts.php');
    const data = await res.json();
    if (!data.success) {
      if (newWrap) newWrap.innerHTML = `<p class="text-red-400 text-center py-8 text-sm">${data.message}</p>`;
      return;
    }
    allContacts = data.contacts || [];

    const pending = allContacts.filter(c => c.status === 'Pending').length;
    const label = document.getElementById('contactCountLabel');
    if (label) label.textContent = `${allContacts.length} total · ${pending} pending`;

    renderContacts(allContacts);
  } catch (e) {
    if (newWrap) newWrap.innerHTML = `<p class="text-red-400 text-center py-8 text-sm">Could not load contacts.</p>`;
  }
}

function buildContactsTable(list) {
  if (!list.length) {
    return `<div class="text-center py-10 text-gray-400">
      <i class="fas fa-inbox text-3xl mb-2 block" style="color:#e2e8f0;"></i>
      <p class="text-sm">No messages here.</p>
    </div>`;
  }
  const statusBadge = s => s === 'Replied'
    ? `<span class="badge" style="background:#dcfce7;color:#16a34a;">Replied</span>`
    : `<span class="badge" style="background:#fef9c3;color:#b45309;">Pending</span>`;

  return `<table class="admin-table">
    <thead>
      <tr>
        <th>#</th><th>User</th><th>Email</th><th>Subject</th>
        <th>Date</th><th>Status</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      ${list.map((c, i) => `
        <tr>
          <td>${i + 1}</td>
          <td><div class="flex items-center gap-2">
            <div class="user-avatar">${(c.user_name || '?')[0].toUpperCase()}</div>
            <span class="font-medium text-gray-700">${escH(c.user_name)}</span>
          </div></td>
          <td class="text-gray-500 text-xs">${escH(c.email)}</td>
          <td class="text-gray-700">${escH(c.subject)}</td>
          <td class="text-gray-500 text-xs">${new Date(c.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
          <td>${statusBadge(c.status)}</td>
          <td>
            ${c.user_reply_unread == 1 ? '<span class="inline-flex items-center gap-1 text-xs font-bold text-red-600 bg-red-50 rounded-full px-2 py-0.5 mb-1"><span class=\"w-2 h-2 rounded-full bg-red-500 inline-block\"></span>User replied</span><br>' : ''}
            <button onclick="openReplyModal(${c.id},'${escA(c.user_name)}','${escA(c.email)}','${escA(c.subject)}','${escA(c.message)}','${escA(c.admin_reply || '')}','${escA(c.user_reply || '')}','${escA(c.user_replied_at || '')}',${c.user_reply_unread || 0})"
              class="text-xs px-3 py-1.5 rounded-lg font-semibold flex items-center gap-1 transition"
              style="background:#eff6ff;color:#2563eb;">
              <i class="fas fa-reply"></i> ${c.status === 'Replied' ? 'View / Edit' : 'Reply'}
            </button>
          </td>
        </tr>`).join('')}
    </tbody>
  </table>`;
}

function renderContacts(list) {
  const newContacts = list.filter(c => isRecentMessage(c.created_at));
  const oldContacts = list.filter(c => !isRecentMessage(c.created_at));

  const newWrap = document.getElementById('newContactsTableWrap');
  const oldWrap = document.getElementById('contactsTableWrap');
  const newCount = document.getElementById('newContactCount');
  const oldCount = document.getElementById('oldContactCount');

  if (newWrap) newWrap.innerHTML = buildContactsTable(newContacts);
  if (oldWrap) oldWrap.innerHTML = buildContactsTable(oldContacts);
  if (newCount) newCount.textContent = newContacts.length;
  if (oldCount) oldCount.textContent = oldContacts.length;

  // Update red-dot on sidebar if any unread user replies exist
  const hasUnread = list.some(c => c.user_reply_unread == 1);
  const dot = document.getElementById('contactsUnreadDot');
  if (dot) {
    if (hasUnread) {
      dot.classList.remove('hidden');
    } else {
      dot.classList.add('hidden');
    }
  }
}

function filterContacts() {
  const q = (document.getElementById('contactSearch')?.value || '').toLowerCase();
  const status = document.getElementById('contactStatusFilter')?.value || '';
  const filtered = allContacts.filter(c => {
    const matchQ = !q ||
      (c.user_name || '').toLowerCase().includes(q) ||
      (c.email || '').toLowerCase().includes(q) ||
      (c.subject || '').toLowerCase().includes(q);
    const matchS = !status || c.status === status;
    return matchQ && matchS;
  });
  renderContacts(filtered);
}

function openReplyModal(id, userName, email, subject, message, existingReply, userReply, userRepliedAt, userReplyUnread) {
  currentReplyId = id;
  document.getElementById('replyMeta').textContent = `From: ${userName} <${email}> — Subject: ${subject}`;
  document.getElementById('replyOriginal').textContent = message;
  document.getElementById('replyText').value = '';
  const prevWrap = document.getElementById('existingReplyWrap');
  const prevText = document.getElementById('existingReplyText');
  if (existingReply) {
    prevText.textContent = existingReply;
    prevWrap.style.display = 'block';
  } else {
    prevWrap.style.display = 'none';
  }
  // Show user follow-up reply if present
  const userFollowUpWrap = document.getElementById('userFollowUpWrap');
  const userFollowUpText = document.getElementById('userFollowUpText');
  const userFollowUpDate = document.getElementById('userFollowUpDate');
  if (userFollowUpWrap && userReply) {
    userFollowUpWrap.style.display = 'block';
    userFollowUpWrap.style.background = '#f0fdf4';
    userFollowUpWrap.style.border = '1.5px solid #bbf7d0';
    userFollowUpText.textContent = userReply;
    userFollowUpDate.textContent = userRepliedAt ? new Date(userRepliedAt.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
    // Mark as read on the server
    if (userReplyUnread == 1) {
      const fd = new FormData();
      fd.append('contact_id', id);
      fetch('../backend/mark_user_reply_read.php', { method: 'POST', body: fd })
        .then(() => {
          // Update dot in-place without full reload
          const allC = (typeof allContacts !== 'undefined') ? allContacts : [];
          const c = allC.find(x => x.id == id);
          if (c) c.user_reply_unread = 0;
          const stillUnread = allC.some(x => x.user_reply_unread == 1);
          const dot = document.getElementById('contactsUnreadDot');
          if (dot) stillUnread ? dot.classList.remove('hidden') : dot.classList.add('hidden');
        })
        .catch(() => { });
    }
  } else if (userFollowUpWrap) {
    userFollowUpWrap.style.display = 'none';
  }
  const modal = document.getElementById('replyModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
}

function closeReplyModal() {
  const modal = document.getElementById('replyModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
  currentReplyId = null;
}

async function submitReply() {
  const replyText = document.getElementById('replyText').value.trim();
  if (!replyText) { toast('Please write a reply first.', true); return; }

  const fd = new FormData();
  fd.append('contact_id', currentReplyId);
  fd.append('admin_reply', replyText);

  try {
    const res = await fetch('../backend/reply_contact.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      toast('Reply sent!');
      closeReplyModal();
      loadContacts();
    } else {
      toast(data.message || 'Failed to send reply.', true);
    }
  } catch (e) {
    toast('Cannot reach server.', true);
  }
}

// Close reply modal on backdrop click
document.addEventListener('click', e => {
  const modal = document.getElementById('replyModal');
  if (modal && e.target === modal) closeReplyModal();
});

function escH(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function escA(str) {
  return String(str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\n/g, '\\n').replace(/\r/g, '');
}

// ═══════════════════════════════════════════════════════
// RECEIVED RECORDS
// ═══════════════════════════════════════════════════════
let allReceivedRecords = [];

async function loadReceivedRecords() {
  const wrap = document.getElementById('receivedRecordsBody');
  if (!wrap) return;
  try {
    const res = await fetch('../backend/get_received_records.php');
    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); } catch { wrap.innerHTML = '<tr><td colspan="13" class="text-center py-6 text-red-400">Server error.</td></tr>'; return; }
    if (data.error) { wrap.innerHTML = `<tr><td colspan="13" class="text-center py-6 text-red-400">DB Error: ${data.error}</td></tr>`; return; }
    allReceivedRecords = Array.isArray(data) ? data : [];
    renderReceivedRecords(allReceivedRecords);
  } catch (e) { wrap.innerHTML = '<tr><td colspan="13" class="text-center py-6 text-red-400">Cannot reach server.</td></tr>'; }
}

function renderReceivedRecords(list) {
  const wrap = document.getElementById('receivedRecordsBody');
  if (!wrap) return;
  if (!list.length) {
    wrap.innerHTML = '<tr><td colspan="13" class="text-center py-10 text-gray-400"><i class="fas fa-inbox text-2xl block mb-2"></i>No received orders yet.</td></tr>';
    return;
  }
  wrap.innerHTML = list.map(r => {
    let items = [];
    try { items = JSON.parse(r.items_json || '[]'); } catch { }
    const itemStr = items.map(i => `${i.name} x${i.qty || 1}`).join(', ') || '—';
    const dt = r.received_at ? new Date(r.received_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
    return `<tr class="hover:bg-gray-50 border-b border-gray-100 text-sm">
      <td class="px-3 py-3 font-medium text-gray-700">${r.id}</td>
      <td class="px-3 py-3 font-semibold" style="color:var(--color-primary);">${r.order_ref || '—'}</td>
      <td class="px-3 py-3">${r.customer_name || '—'}</td>
      <td class="px-3 py-3 text-gray-500">${r.customer_email || '—'}</td>
      <td class="px-3 py-3 text-gray-500">${r.customer_phone || '—'}</td>
      <td class="px-3 py-3 text-gray-500" style="min-width:200px;white-space:normal;word-break:break-word;">${r.address || '—'}</td>
      <td class="px-3 py-3 text-gray-500 max-w-[180px] truncate" title="${itemStr}">${itemStr}</td>
      <td class="px-3 py-3 text-right">₱${parseFloat(r.subtotal || 0).toFixed(2)}</td>
      <td class="px-3 py-3 text-right">₱${parseFloat(r.shipping || 0).toFixed(2)}</td>
      <td class="px-3 py-3 text-right font-semibold" style="color:var(--color-primary);">₱${parseFloat(r.total || 0).toFixed(2)}</td>
      <td class="px-3 py-3">${r.payment_method || '—'}</td>
      <td class="px-3 py-3">${r.service || '—'}</td>
      <td class="px-3 py-3 text-gray-500 whitespace-nowrap">${dt}</td>
    </tr>`;
  }).join('');
}

const receivedSearch = document.getElementById('receivedSearch');
if (receivedSearch) {
  receivedSearch.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    renderReceivedRecords(!q ? allReceivedRecords : allReceivedRecords.filter(r =>
      (r.order_ref || '').toLowerCase().includes(q) ||
      (r.customer_name || '').toLowerCase().includes(q) ||
      (r.customer_email || '').toLowerCase().includes(q)
    ));
  });
}
/* ═══════════════════════════════════════════════════════════
   FORUM REPORTS
═══════════════════════════════════════════════════════════ */
let allReports = [];
let currentReportId = null;

async function loadReports() {
  const wrap = document.getElementById('reportsTableWrap');
  if (wrap) wrap.innerHTML = '<div class="text-center py-10 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i><p class="text-sm">Loading reports…</p></div>';

  try {
    const res = await fetch('../backend/get_reports.php');
    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (_) {
      if (wrap) wrap.innerHTML = `<p class="text-red-400 text-center py-8 text-sm">Server error:<br><code style="font-size:11px;white-space:pre-wrap;">${text.slice(0, 500)}</code></p>`;
      return;
    }
    if (!data.success) {
      if (wrap) wrap.innerHTML = `<p class="text-red-400 text-center py-8 text-sm">Error: ${data.message || 'Unknown error'}</p>`;
      return;
    }
    allReports = data.reports || [];

    const label = document.getElementById('reportsCountLabel');
    if (label) label.textContent = `${allReports.length} total · ${data.pending || 0} pending`;

    // Update sidebar badge
    const badge = document.getElementById('reportsBadge');
    if (badge) {
      if ((data.pending || 0) > 0) {
        badge.textContent = data.pending;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    renderReports(allReports);
  } catch (e) {
    if (wrap) wrap.innerHTML = '<p class="text-red-400 text-center py-8 text-sm">Could not load reports.</p>';
  }
}

function renderReports(list) {
  const wrap = document.getElementById('reportsTableWrap');
  if (!wrap) return;

  if (!list.length) {
    wrap.innerHTML = `<div class="text-center py-14 text-gray-400">
      <i class="fas fa-flag text-4xl mb-3 block" style="color:#e2e8f0;"></i>
      <p class="text-sm font-semibold">No reports found.</p>
    </div>`;
    return;
  }

  const statusBadge = s => {
    if (s === 'Reviewed') return `<span class="badge" style="background:#dcfce7;color:#16a34a;">Reviewed</span>`;
    if (s === 'Dismissed') return `<span class="badge" style="background:#f3f4f6;color:#6b7280;">Dismissed</span>`;
    return `<span class="badge" style="background:#fef9c3;color:#b45309;">Pending</span>`;
  };

  wrap.innerHTML = `<table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Reporter</th>
        <th>Reported User</th>
        <th>Post Title</th>
        <th>Reason</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      ${list.map((r, i) => `
        <tr>
          <td>${i + 1}</td>
          <td><div class="flex items-center gap-2">
            <div class="user-avatar">${(r.reporter_name || '?')[0].toUpperCase()}</div>
            <div>
              <p class="font-medium text-gray-700 text-xs">${escH(r.reporter_name || '—')}</p>
              <p class="text-gray-400 text-xs">${escH(r.reporter_email || '')}</p>
            </div>
          </div></td>
          <td><div class="flex items-center gap-2">
            <div class="user-avatar" style="background:#fee2e2;color:#dc2626;">${(r.reported_name || '?')[0].toUpperCase()}</div>
            <div>
              <p class="font-medium text-gray-700 text-xs">${escH(r.reported_name || '—')}</p>
              <p class="text-gray-400 text-xs">${escH(r.reported_email || '')}</p>
            </div>
          </div></td>
          <td class="text-gray-600 text-xs max-w-[160px] truncate" title="${escH(r.post_title || '')}">${escH(r.post_title || '(deleted)')}</td>
          <td><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#fef3c7;color:#92400e;">${escH(r.reason)}</span></td>
          <td class="text-gray-400 text-xs">${new Date(r.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
          <td>${statusBadge(r.status)}</td>
          <td>
            <button onclick="openViewReportModal(${r.id})"
              class="text-xs px-3 py-1.5 rounded-lg font-semibold flex items-center gap-1 transition"
              style="background:#eff6ff;color:#2563eb;">
              <i class="fas fa-eye"></i> View
            </button>
          </td>
        </tr>`).join('')}
    </tbody>
  </table>`;
}

function filterReports() {
  const q = (document.getElementById('reportSearch')?.value || '').toLowerCase();
  const status = document.getElementById('reportStatusFilter')?.value || '';
  const filtered = allReports.filter(r => {
    const matchQ = !q ||
      (r.reporter_name || '').toLowerCase().includes(q) ||
      (r.reported_name || '').toLowerCase().includes(q) ||
      (r.reporter_email || '').toLowerCase().includes(q) ||
      (r.reason || '').toLowerCase().includes(q) ||
      (r.post_title || '').toLowerCase().includes(q);
    const matchStatus = !status || r.status === status;
    return matchQ && matchStatus;
  });
  renderReports(filtered);
}

function openViewReportModal(id) {
  const r = allReports.find(x => x.id == id);
  if (!r) return;
  currentReportId = id;

  const statusColor = { Pending: '#b45309', Reviewed: '#16a34a', Dismissed: '#6b7280' };
  const statusBg = { Pending: '#fef9c3', Reviewed: '#dcfce7', Dismissed: '#f3f4f6' };

  document.getElementById('viewReportBody').innerHTML = `
    <div class="space-y-4 text-sm">
      <div class="flex items-center justify-between">
        <span class="font-semibold text-gray-500 uppercase text-xs tracking-wide">Status</span>
        <span class="px-3 py-1 rounded-full font-bold text-xs" style="background:${statusBg[r.status]};color:${statusColor[r.status]};">${r.status}</span>
      </div>
      <div class="bg-gray-50 rounded-xl p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Reporter</p>
        <p class="font-semibold text-gray-800">${escH(r.reporter_name || '—')}</p>
        <p class="text-gray-400 text-xs">${escH(r.reporter_email || '')}</p>
      </div>
      <div class="bg-red-50 rounded-xl p-4">
        <p class="text-xs font-semibold text-red-400 uppercase tracking-wide mb-2">Reported User</p>
        <p class="font-semibold text-gray-800">${escH(r.reported_name || '—')}</p>
        <p class="text-gray-400 text-xs">${escH(r.reported_email || '')}</p>
      </div>
      <div class="bg-yellow-50 rounded-xl p-4">
        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">Reason</p>
        <p class="font-bold text-yellow-800">${escH(r.reason)}</p>
      </div>
      <div class="bg-gray-50 rounded-xl p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Post</p>
        <p class="font-semibold text-gray-700">${escH(r.post_title || '(deleted)')}</p>
        ${r.post_content ? `<p class="text-gray-500 text-xs mt-1 line-clamp-3">${escH(r.post_content.slice(0, 200))}${r.post_content.length > 200 ? '…' : ''}</p>` : ''}
      </div>
      <div>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Date Reported</p>
        <p class="text-gray-600">${new Date(r.created_at).toLocaleString('en-PH')}</p>
      </div>
      <div>
        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 block">Admin Note (optional)</label>
        <textarea id="reportAdminNote" rows="3" class="form-input w-full" style="resize:vertical;" placeholder="Add a note about how this was handled…">${escH(r.admin_note || '')}</textarea>
      </div>
    </div>`;

  const modal = document.getElementById('viewReportModal');
  modal.classList.remove('hidden');
  modal.style.display = 'flex';
}

function closeViewReportModal() {
  const modal = document.getElementById('viewReportModal');
  modal.classList.add('hidden');
  modal.style.display = 'none';
  currentReportId = null;
}

async function updateReportStatus(status) {
  if (!currentReportId) return;
  const note = document.getElementById('reportAdminNote')?.value.trim() || '';

  const fd = new FormData();
  fd.append('id', currentReportId);
  fd.append('status', status);
  fd.append('admin_note', note);

  try {
    const res = await fetch('../backend/update_report.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      closeViewReportModal();
      await loadReports();
    } else {
      alert(data.message || 'Failed to update report.');
    }
  } catch (_) {
    alert('Network error. Please try again.');
  }
}

// Load reports badge count on admin init
(async function initReportsBadge() {
  try {
    const res = await fetch('../backend/get_reports.php');
    const data = await res.json();
    const badge = document.getElementById('reportsBadge');
    if (badge && (data.pending || 0) > 0) {
      badge.textContent = data.pending;
      badge.classList.remove('hidden');
    }
  } catch (_) { }
})();
// ─── WARN USER ──────────────────────────────────────────────────────────────
function openWarnUserModal() {
  const r = allReports.find(x => x.id == currentReportId);
  if (!r) return;
  document.getElementById('warnTargetName').textContent = r.reported_name || 'Unknown User';
  document.getElementById('warnMessageText').value = '';
  const errEl = document.getElementById('warnError');
  if (errEl) errEl.style.display = 'none';
  const modal = document.getElementById('warnUserModal');
  modal.classList.remove('hidden');
  modal.style.display = 'flex';
}

function closeWarnUserModal() {
  const modal = document.getElementById('warnUserModal');
  modal.classList.add('hidden');
  modal.style.display = 'none';
}

async function submitWarnUser() {
  const r = allReports.find(x => x.id == currentReportId);
  if (!r) return;
  const msg = (document.getElementById('warnMessageText')?.value || '').trim();
  const errEl = document.getElementById('warnError');
  if (!msg) {
    if (errEl) { errEl.textContent = 'Please enter a warning message.'; errEl.style.display = 'block'; }
    return;
  }
  const fd = new FormData();
  fd.append('user_id', r.reported_id);
  fd.append('report_id', currentReportId);
  fd.append('warn_message', msg);
  try {
    const res = await fetch('../backend/warn_user.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      closeWarnUserModal();
      closeViewReportModal();
      await loadReports();
      showAdminToast('Warning sent to ' + (r.reported_name || 'user') + '.');
    } else {
      if (errEl) { errEl.textContent = data.message || 'Failed to send warning.'; errEl.style.display = 'block'; }
    }
  } catch (_) {
    if (errEl) { errEl.textContent = 'Network error. Please try again.'; errEl.style.display = 'block'; }
  }
}