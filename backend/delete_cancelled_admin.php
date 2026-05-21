<!DOCTYPE html>
<html lang="eng">
<head>
  <title>Technilog Admin Panel</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../stylesheet/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <style>
    * { box-sizing: border-box; }
    body { background: #f1f5f9; margin: 0; }

    #sidebar {
      position: fixed; top: 64px; left: 0; bottom: 0;
      width: 225px; background: #fff;
      box-shadow: 2px 0 10px rgba(0,0,0,.06);
      display: flex; flex-direction: column;
      padding: 1rem 0; z-index: 40; overflow-y: auto;
    }
    .sidebar-section-label {
      padding: 10px 22px 4px;
      font-size: .68rem; font-weight: 700; letter-spacing: 1px;
      color: #94a3b8; text-transform: uppercase;
    }
    .sidebar-divider { height: 1px; background: #f1f5f9; margin: 6px 16px; }
    .sidebar-link {
      display: flex; align-items: center; gap: 12px;
      padding: 10px 22px; color: #64748b;
      font-size: .875rem; font-weight: 500;
      text-decoration: none; transition: all .15s;
      border-left: 3px solid transparent;
    }
    .sidebar-link:hover { background: #f8fafc; color: var(--color-primary); }
    .sidebar-link.active {
      background: #f0f7ff; color: var(--color-primary);
      border-left-color: var(--color-primary); font-weight: 600;
    }
    .sidebar-link i { width: 18px; text-align: center; }

    #mainContent { margin-left: 225px; padding-top: 64px; min-height: 100vh; }
    .panel { display: none; padding: 2rem; }
    .panel.active { display: block; }

    .stat-card {
      background: #fff; border-radius: 16px;
      padding: 1.2rem 1.4rem;
      display: flex; align-items: center; gap: 1rem;
      box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .stat-icon {
      width: 46px; height: 46px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 1.1rem; flex-shrink: 0;
    }

    .admin-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
    .admin-table thead th {
      background: #f8fafc; padding: 11px 14px;
      text-align: left; font-weight: 600; color: #475569;
      border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .admin-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .12s; }
    .admin-table tbody tr:last-child { border-bottom: none; }
    .admin-table tbody tr:hover { background: #f8fafc; }
    .admin-table td { padding: 10px 14px; color: #374151; vertical-align: middle; }

    /* Hover tooltip for truncated cells */
    .tl-tip { position: relative; cursor: default; }
    .tl-tip .tl-tip-box {
      display: none;
      position: absolute;
      left: 0; top: calc(100% + 6px);
      background: #1e293b;
      color: #fff;
      font-size: .78rem;
      font-weight: 500;
      padding: .45rem .75rem;
      border-radius: .5rem;
      white-space: normal;
      max-width: 280px;
      min-width: 140px;
      word-break: break-word;
      z-index: 9999;
      line-height: 1.45;
      box-shadow: 0 4px 16px rgba(0,0,0,.25);
      pointer-events: none;
    }
    .tl-tip:hover .tl-tip-box { display: block; }

    .badge {
      display: inline-block; padding: 3px 10px; border-radius: 999px;
      font-size: .72rem; font-weight: 600; white-space: nowrap;
    }
    .badge-verified   { background: #dcfce7; color: #16a34a; }
    .badge-pending    { background: #fef9c3; color: #b45309; }
    .badge-paid       { background: #dcfce7; color: #16a34a; }
    .badge-cancelled  { background: #fee2e2; color: #dc2626; }
    .badge-shipped    { background: #e0f2fe; color: #0369a1; }
    .badge-processing { background: #ede9fe; color: #7c3aed; }

    .user-avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: var(--color-primary); color: #fff;
      display: inline-flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: .8rem; flex-shrink: 0;
      text-transform: uppercase;
    }

    .search-bar {
      display: flex; align-items: center; gap: 9px;
      background: #f8fafc; border: 1px solid #e2e8f0;
      border-radius: 10px; padding: 8px 13px; width: 240px;
    }
    .search-bar input { border: none; background: transparent; outline: none; font-size: .875rem; color: #374151; width: 100%; }
    .search-bar i { color: #94a3b8; }

    #productsGrid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 1.25rem;
    }
    .prod-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,.07); display: flex; flex-direction: column; }
    .prod-card-img { width: 100%; height: 150px; object-fit: cover; background: #f1f5f9; }
    .prod-card-body { padding: 1rem; flex: 1; }
    .prod-card-name { font-weight: 700; color: #1e293b; margin-bottom: 2px; }
    .prod-card-desc { font-size: .8rem; color: #64748b; margin-bottom: 6px; }
    .prod-card-price { font-size: 1rem; font-weight: 700; color: var(--color-primary); }
    .prod-card-actions { display: flex; gap: 8px; padding: .75rem 1rem; border-top: 1px solid #f1f5f9; }
    .btn-edit   { flex: 1; padding: 6px; border-radius: 8px; background: #eff6ff; color: #2563eb; font-size: .8rem; font-weight: 600; cursor: pointer; border: none; transition: background .15s; }
    .btn-edit:hover { background: #dbeafe; }
    .btn-delete { flex: 1; padding: 6px; border-radius: 8px; background: #fff5f5; color: #dc2626; font-size: .8rem; font-weight: 600; cursor: pointer; border: none; transition: background .15s; }
    .btn-delete:hover { background: #fee2e2; }

    .form-label { display: block; font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: 5px; }
    .form-input {
      width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0;
      border-radius: 10px; font-size: .875rem; color: #1e293b;
      outline: none; transition: border .15s; background: #fff;
    }
    .form-input:focus { border-color: var(--color-primary); }

    #dropZone {
      border: 2px dashed #cbd5e1; border-radius: 14px;
      padding: 2rem; text-align: center; cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    #dropZone:hover, #dropZone.drag-over { border-color: var(--color-primary); background: #f0f7ff; }
    #imagePreviewGrid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
    .img-thumb { position: relative; width: 80px; height: 80px; border-radius: 10px; overflow: hidden; border: 1.5px solid #e2e8f0; }
    .img-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .remove-img { position: absolute; top: 3px; right: 3px; width: 20px; height: 20px; border-radius: 50%; background: #dc2626; color: #fff; border: none; font-size: .6rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }

    .price-row { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; flex-wrap: wrap; }
    .remove-price-btn { width: 32px; height: 32px; border: none; border-radius: 8px; background: #fee2e2; color: #dc2626; cursor: pointer; font-size: .8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

    #toast {
      position: fixed; bottom: 28px; right: 28px;
      padding: 12px 22px; border-radius: 12px;
      color: #fff; font-weight: 600; font-size: .875rem;
      opacity: 0; pointer-events: none; transform: translateY(10px);
      transition: opacity .3s, transform .3s; z-index: 9999;
    }
    #toast.show { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>

<!-- HEADER -->
<header class="fixed top-0 left-0 right-0 z-50 flex items-center px-5 h-16" style="background-color:var(--color-primary);">
  <div class="flex items-center gap-3">
    <img src="../image/logo.png" alt="Logo" class="h-10 rounded-full">
    <span class="text-white font-bold text-xl tracking-wide italic">ADMIN PANEL</span>
  </div>
  <div class="ml-auto flex items-center gap-3">
    <span class="text-white/70 text-sm" id="adminEmail"></span>
    <a href="../frontend/main.html" class="text-white/80 hover:text-white text-sm transition">
      <i class="fas fa-arrow-left mr-1"></i>Back to Site
    </a>
  </div>
</header>

<!-- SIDEBAR -->
<div id="sidebar">
  <span class="sidebar-section-label">Overview</span>
  <a href="#" class="sidebar-link active" data-panel="dashboard">
    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
  </a>

  <div class="sidebar-divider"></div>
  <span class="sidebar-section-label">Management</span>
  <a href="#" class="sidebar-link" data-panel="users">
    <i class="fa-solid fa-users"></i><span>Users/Admins</span>
  </a>
  <a href="#" class="sidebar-link" data-panel="products">
    <i class="fa-solid fa-boxes-stacked"></i><span>Products</span>
  </a>
  <a href="#" class="sidebar-link" data-panel="addProduct">
    <i class="fa-solid fa-plus"></i><span>Add Product</span>
  </a>
  <a href="#" class="sidebar-link" data-panel="inventory">
    <i class="fa-solid fa-warehouse"></i><span>Inventory</span>
  </a>

  <div class="sidebar-divider"></div>
  <span class="sidebar-section-label">Orders & Support</span>
  <a href="#" class="sidebar-link" data-panel="deliveries">
    <i class="fa-solid fa-truck"></i><span>Deliveries</span>
  </a>
<a href="#" class="sidebar-link" data-panel="receivedRecords">
    <i class="fa-solid fa-circle-check"></i><span>Received</span>
  </a>
  <a href="#" class="sidebar-link" data-panel="cancelledOrders">
    <i class="fas fa-ban"></i><span>Cancelled</span>
  </a>
  <a href="#" class="sidebar-link" data-panel="contacts">
    <i class="fa-solid fa-envelope"></i><span>Contacts</span>
  </a>
</div>

<!-- MAIN CONTENT -->
<div id="mainContent">

  <!-- ══ DASHBOARD ══ -->
  <div class="panel active" id="panel-dashboard">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back! Here's your Technilog overview.</p>
      </div>
      <span class="text-sm text-gray-400 font-medium" id="dashDate"></span>
    </div>

    <!-- Revenue & Orders Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-4">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--color-primary);"><i class="fas fa-peso-sign"></i></div>
        <div>
          <div class="text-lg font-bold text-gray-800 leading-tight" id="statRevenue">₱0.00</div>
          <div class="text-xs text-gray-500 mt-0.5">Total Revenue</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#10b981;"><i class="fas fa-shopping-cart"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="statOrders">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Total Orders</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#f59e0b;"><i class="fas fa-clock"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="statPendingOrders">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Pending Orders</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#8b5cf6;"><i class="fas fa-peso-sign"></i></div>
        <div>
          <div class="text-lg font-bold text-gray-800 leading-tight" id="statMonthRevenue">₱0.00</div>
          <div class="text-xs text-gray-500 mt-0.5">Revenue This Month</div>
        </div>
      </div>
    </div>

    <!-- Users & Products Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
      <div class="stat-card">
        <div class="stat-icon" style="background:#06b6d4;"><i class="fas fa-users"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="statUsers">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Total Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--color-secondary);"><i class="fas fa-box"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="statProducts">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Products</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#10b981;"><i class="fas fa-check-circle"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="statVerified">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Verified Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#f43f5e;"><i class="fas fa-triangle-exclamation"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="statLowStock">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Low Stock Items</div>
        </div>
      </div>
    </div>

    <!-- Sales Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Daily Sales -->
      <div class="bg-white rounded-2xl shadow p-5">
        <h2 class="font-bold text-gray-700 text-base mb-4">Daily Sales</h2>
        <canvas id="dailySalesChart" width="400" height="200"></canvas>
      </div>

      <!-- Monthly Revenue -->
      <div class="bg-white rounded-2xl shadow p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-gray-700 text-base">Monthly Revenue</h2>
          <div class="flex items-center">
            <button id="prevYear" class="text-gray-500 hover:text-gray-700 text-lg mr-2"><i class="fas fa-chevron-left"></i></button>
            <span id="currentYear" class="text-lg font-semibold">2026</span>
            <button id="nextYear" class="text-gray-500 hover:text-gray-700 text-lg ml-2"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
        <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl shadow p-5 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-gray-700 text-base">Recent Orders</h2>
        <button onclick="showPanel('deliveries')" class="text-sm font-semibold hover:underline" style="color:var(--color-primary);">
          View All <i class="fas fa-arrow-right ml-1"></i>
        </button>
      </div>
      <div class="overflow-x-auto" id="recentOrdersTable">
        <div class="text-center text-gray-400 py-10">
          <i class="fas fa-shopping-bag text-3xl mb-3 block" style="color:#e2e8f0;"></i>
          <p class="text-sm">No orders yet.</p>
        </div>
      </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-white rounded-2xl shadow p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-gray-700 text-base">Recent Users</h2>
        <button onclick="showPanel('users')" class="text-sm font-semibold hover:underline" style="color:var(--color-primary);">
          View All <i class="fas fa-arrow-right ml-1"></i>
        </button>
      </div>
      <div id="recentUsersTable"></div>
    </div>
  </div>

  <!-- ══ USERS ══ -->
  <div class="panel" id="panel-users">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Users</h1>
        <p class="text-sm text-gray-500 mt-1" id="userCountLabel">Loading…</p>
      </div>
      <div class="flex items-center gap-3 flex-wrap">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" id="userSearch" placeholder="Search name or email…" oninput="filterUsers()">
        </div>
        <select id="userStatusFilter" onchange="filterUsers()" class="form-input" style="width:auto;padding:8px 12px;">
          <option value="">All Users</option>
          <option value="verified">Verified</option>
          <option value="pending">Pending</option>
          <option value="admin">Admin</option>
          <option value="banned">Banned</option>
        </select>
      </div>
    </div>

    <!-- NEW USERS (within 7 days) -->
    <div class="mb-5">
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#dcfce7;color:#15803d;">
          <i class="fas fa-user-plus"></i> New Users <span id="newUserCount" class="ml-1 bg-green-600 text-white rounded-full px-2 py-0.5 text-xs">0</span>
        </span>
        <span class="text-xs text-gray-400">Accounts created within the last 7 days</span>
      </div>
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div id="newUsersTableWrap" class="overflow-x-auto"></div>
      </div>
    </div>

    <!-- ALL / OLDER USERS -->
    <div>
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#f3f4f6;color:#374151;">
          <i class="fas fa-users"></i> All Users <span id="oldUserCount" class="ml-1 bg-gray-500 text-white rounded-full px-2 py-0.5 text-xs">0</span>
        </span>
        <span class="text-xs text-gray-400">Accounts older than 7 days</span>
      </div>
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div id="usersTableWrap" class="overflow-x-auto"></div>
      </div>
    </div>

    <!-- ADMINS -->
    <div class="mt-5">
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#ede9fe;color:#5b21b6;">
          <i class="fas fa-shield-halved"></i> Admins <span id="adminUserCount" class="ml-1 bg-purple-700 text-white rounded-full px-2 py-0.5 text-xs">0</span>
        </span>
        <span class="text-xs text-gray-400">Accounts with administrator role</span>
      </div>
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div id="adminUsersTableWrap" class="overflow-x-auto"></div>
      </div>
    </div>
  </div>

  <!-- ══ PRODUCTS ══ -->
  <div class="panel" id="panel-products">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Products</h1>
      <button onclick="showPanel('addProduct')" class="px-4 py-2 rounded-lg text-white font-semibold text-sm transition hover:opacity-90" style="background:var(--color-primary);">
        <i class="fas fa-plus mr-1"></i> Add Product
      </button>
    </div>
    <div id="productsGrid"><p class="text-gray-400 col-span-full text-center py-12">No products yet.</p></div>
  </div>

  <!-- ══ ADD PRODUCT ══ -->
  <div class="panel" id="panel-addProduct">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Add Product</h1>
    <div class="bg-white rounded-2xl shadow p-6 max-w-3xl">
      <div class="mb-5">
        <label class="form-label">Product Images</label>
        <input type="file" id="imgInput" accept="image/*" multiple style="display:none;">
        <div id="dropZone">
          <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
          <p class="text-gray-500 text-sm">Click to upload or drag & drop images</p>
        </div>
        <div id="imagePreviewGrid"></div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="form-label">Product Name *</label>
          <input class="form-input" id="prodName" type="text" placeholder="e.g. 4MP Smart Camera">
        </div>
        <div>
          <label class="form-label">Category</label>
          <select class="form-input" id="prodCategory">
            <option>Smart CCTV Camera</option>
            <option>Smart Fire Alarm</option>
            <option>Accessories</option>
            <option>Other</option>
          </select>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label">Description *</label>
        <textarea class="form-input" id="prodDesc" rows="3" placeholder="Describe the product…" style="resize:vertical;"></textarea>
      </div>
      <div class="mb-4">
        <label class="form-label">Specifications (optional)</label>
        <textarea class="form-input" id="prodSpecs" rows="2" placeholder="e.g. Resolution: 4MP, Night Vision: 30m, IP Rating: IP67" style="resize:vertical;"></textarea>
      </div>
      <div class="mb-5">
        <div class="flex items-center justify-between mb-2">
          <label class="form-label" style="margin:0;">Prices / Variants *</label>
          <button id="addPriceBtn" class="text-sm px-3 py-1 rounded-lg font-semibold transition" style="background:var(--color-quarternary);color:var(--color-primary);">
            <i class="fas fa-plus mr-1"></i> Add Variant
          </button>
        </div>
        <div id="priceTiers">
          <div class="price-row">
            <input class="form-input" type="text" placeholder="Label (e.g. Standard, 2MP)" style="max-width:180px;">
            <input class="form-input" type="number" placeholder="Price (₱)" min="0" step="0.01">
            <input class="form-input" type="number" placeholder="Stock" min="0">
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div>
          <label class="form-label">Status</label>
          <select class="form-input" id="prodStatus">
            <option>Available</option>
            <option>Out of Stock</option>
            <option>Coming Soon</option>
          </select>
        </div>
        <div>
          <label class="form-label">Featured?</label>
          <select class="form-input" id="prodFeatured">
            <option value="0">No</option>
            <option value="1">Yes — show on homepage</option>
          </select>
        </div>
      </div>
      <div class="flex gap-3">
        <button id="saveProdBtn" class="px-6 py-2 rounded-lg text-white font-semibold transition hover:opacity-90" style="background:var(--color-primary);">
          <i class="fas fa-save mr-1"></i> Save Product
        </button>
        <button onclick="showPanel('products')" class="px-6 py-2 rounded-lg font-semibold transition hover:bg-gray-100" style="background:#f3f4f6;color:#374151;">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- ══ INVENTORY ══ -->
  <div class="panel" id="panel-inventory">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Inventory</h1>
        <p class="text-sm text-gray-500 mt-1">View and manage stock levels for all products and variants.</p>
      </div>
      <div class="flex items-center gap-3 flex-wrap">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" id="invSearch" placeholder="Search products…" oninput="filterInventory()">
        </div>
        <select id="invStockFilter" class="form-input" style="width:auto;padding:8px 12px;" onchange="filterInventory()">
          <option value="">All Stock</option>
          <option value="low">Low Stock (≤5)</option>
          <option value="out">Out of Stock</option>
          <option value="ok">In Stock</option>
        </select>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid gap-4 mb-6" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
      <div class="stat-card">
        <div class="stat-icon" style="background:#6366f1;"><i class="fas fa-boxes-stacked"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="invTotalVariants">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Total Variants</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#10b981;"><i class="fas fa-circle-check"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="invInStock">—</div>
          <div class="text-xs text-gray-500 mt-0.5">In Stock</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#f59e0b;"><i class="fas fa-triangle-exclamation"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="invLowStock">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Low Stock (≤5)</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:#ef4444;"><i class="fas fa-ban"></i></div>
        <div>
          <div class="text-2xl font-bold text-gray-800" id="invOutStock">—</div>
          <div class="text-xs text-gray-500 mt-0.5">Out of Stock</div>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div id="inventoryTableWrap">
        <div class="text-center py-12 text-gray-400">
          <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
          <p class="text-sm">Loading inventory…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Stock Edit Modal -->
  <div id="stockModal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.45);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-800 text-lg">Edit Stock</h2>
        <button onclick="closeStockModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
      </div>
      <div class="px-6 py-5">
        <p class="text-sm text-gray-500 mb-1" id="stockModalProduct"></p>
        <p class="text-sm font-semibold text-gray-700 mb-4" id="stockModalVariant"></p>
        <label class="form-label">Stock Quantity</label>
        <input type="number" id="stockModalQty" class="form-input" min="0" placeholder="Enter stock amount">
        <input type="hidden" id="stockModalProductId">
        <input type="hidden" id="stockModalVariantIdx">
      </div>
      <div class="flex gap-3 px-6 pb-5">
        <button onclick="closeStockModal()" class="flex-1 py-2 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">Cancel</button>
        <button onclick="saveStock()" class="flex-1 py-2 rounded-xl text-white font-semibold text-sm transition" style="background:var(--color-primary);">Save Stock</button>
      </div>
    </div>
  </div>

  <!-- ══ DELIVERIES ══ -->
  <div class="panel" id="panel-deliveries">
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Deliveries</h1>
        <p class="text-sm text-gray-500 mt-1">Manage and update order statuses.</p>
      </div>
      <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="deliverySearch" placeholder="Search by name, ref, email…">
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div style="overflow-x:auto;">
        <table class="admin-table" style="min-width:1300px;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Address</th>
              <th>Notes</th>
              <th>Payment</th>
              <th>Service</th>
              <th style="text-align:right">Install Fee</th>
              <th>Items</th>
              <th style="text-align:right">Subtotal</th>
              <th style="text-align:right">Shipping</th>
              <th style="text-align:right">Total</th>
              <th>Status</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody id="deliveriesTableBody">
            <tr><td colspan="16" class="text-center py-10 text-gray-400">
              <i class="fas fa-spinner fa-spin mr-2"></i>Loading…
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ══ CANCELLED ORDERS ══ -->
  <div class="panel" id="panel-cancelledOrders">
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Cancelled Orders</h1>
        <p class="text-sm text-gray-500 mt-1">All orders cancelled by customers.</p>
      </div>
      <div class="flex items-center gap-3 flex-wrap">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" id="cancelledSearch" placeholder="Search name, ref, email…" oninput="filterCancelled()">
        </div>
        <button id="clearAllCancelledBtn"
          class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white transition"
          style="background:#ef4444;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">
          <i class="fas fa-trash-can"></i> Delete All
        </button>
      </div>
    </div>

    <!-- Summary strip -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Total Cancelled</p>
        <p class="text-3xl font-bold text-gray-800" id="cancelledCount">—</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Lost Revenue</p>
        <p class="text-3xl font-bold text-red-500" id="cancelledRevenue">—</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Unique Customers</p>
        <p class="text-3xl font-bold text-gray-800" id="cancelledCustomers">—</p>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div style="overflow-x:auto;">
        <table class="admin-table" style="min-width:1100px;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Username</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Items</th>
              <th style="text-align:right">Total</th>
              <th>Payment</th>
              <th>Address</th>
              <th>Date Cancelled</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="cancelledTableBody">
            <tr><td colspan="12" class="text-center py-10 text-gray-400">
              <i class="fas fa-spinner fa-spin mr-2"></i>Loading…
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ══ RECEIVED RECORDS ══ -->
  <div class="panel" id="panel-receivedRecords">

    <!-- Header row -->
    <div class="flex items-start justify-between mb-6 flex-wrap gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Received Orders</h1>
        <p class="text-sm text-gray-500 mt-1">Orders confirmed received by customers.</p>
      </div>
      <!-- Month/Year picker (top-right) -->
      <div class="flex items-center gap-2 flex-wrap">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" id="receivedSearch" placeholder="Search name, ref, email…" oninput="filterReceived()">
        </div>
        <select id="recMonthPicker" onchange="renderRecordsTable()" class="form-input" style="width:auto;padding:8px 14px;font-weight:600;">
          <option value="0">January</option><option value="1">February</option>
          <option value="2">March</option><option value="3">April</option>
          <option value="4">May</option><option value="5">June</option>
          <option value="6">July</option><option value="7">August</option>
          <option value="8">September</option><option value="9">October</option>
          <option value="10">November</option><option value="11">December</option>
        </select>
        <select id="recYearPicker" onchange="renderRecordsTable()" class="form-input" style="width:auto;padding:8px 14px;font-weight:600;"></select>
      </div>
    </div>

    <!-- Summary stats — updates with picker -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">
          Orders — <span id="recStatsLabel" class="normal-case text-indigo-500">this month</span>
        </p>
        <p class="text-3xl font-bold text-gray-800" id="recStatCount">—</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Revenue</p>
        <p class="text-3xl font-bold" style="color:var(--color-primary);" id="recStatRevenue">—</p>
      </div>
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Avg. Order Value</p>
        <p class="text-3xl font-bold text-gray-800" id="recStatAvg">—</p>
      </div>
    </div>

    <!-- ── Received Orders table (full list, all time) ── -->
    <div class="mb-2 flex items-center gap-2">
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#d1fae5;color:#065f46;">
        <i class="fas fa-circle-check"></i> Received Orders
        <span id="recAllCount" class="ml-1 bg-emerald-600 text-white rounded-full px-2 py-0.5 text-xs">0</span>
      </span>
      <span class="text-xs text-gray-400">All orders marked received by customers</span>
    </div>
    <div class="bg-white rounded-2xl shadow overflow-hidden mb-8">
      <div style="overflow-x:auto;">
        <table class="admin-table" style="min-width:1100px;">
          <thead><tr>
            <th>#</th><th>Order Ref</th><th>Customer</th><th>Email</th>
            <th>Phone</th><th>Items</th>
            <th style="text-align:right">Subtotal</th>
            <th style="text-align:right">Shipping</th>
            <th style="text-align:right">Total</th>
            <th>Payment</th><th>Received At</th>
          </tr></thead>
          <tbody id="receivedRecordsBody">
            <tr><td colspan="11" class="text-center py-10 text-gray-400">
              <i class="fas fa-spinner fa-spin mr-2"></i>Loading…
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Monthly Records table ── -->
    <div class="mb-2 flex items-center gap-2">
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#dbeafe;color:#1d4ed8;">
        <i class="fas fa-calendar-days"></i> Monthly Records
        <span id="recMonthCount" class="ml-1 bg-blue-600 text-white rounded-full px-2 py-0.5 text-xs">0</span>
      </span>
      <span class="text-xs text-gray-400" id="recMonthLabel">Records for this month</span>
    </div>
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <div style="overflow-x:auto;">
        <table class="admin-table" style="min-width:900px;">
          <thead><tr>
            <th>#</th><th>Order Ref</th><th>Customer</th><th>Email</th>
            <th>Items</th>
            <th style="text-align:right">Total</th>
            <th>Payment</th><th>Day</th><th>Received At</th>
          </tr></thead>
          <tbody id="receivedMonthBody">
            <tr><td colspan="9" class="text-center py-10 text-gray-400">
              Select a month above to view records.
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Download button -->
    <div class="flex justify-end mt-4">
      <button onclick="downloadMonthlyExcel()"
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white shadow-md transition hover:opacity-90 active:scale-95"
        style="background: linear-gradient(135deg, #16a34a, #15803d);">
        <i class="fas fa-file-excel"></i>
        Download Excel
      </button>
    </div>

  </div>

  <!-- ══ CONTACTS ══ -->
  <div class="panel" id="panel-contacts">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Contact Messages</h1>
        <p class="text-sm text-gray-500 mt-1" id="contactCountLabel">Loading…</p>
      </div>
      <div class="flex items-center gap-3 flex-wrap">
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" id="contactSearch" placeholder="Search name, email, subject…" oninput="filterContacts()">
        </div>
        <select id="contactStatusFilter" onchange="filterContacts()" class="form-input" style="width:auto;padding:8px 12px;">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Replied">Replied</option>
        </select>
        <select id="contactAgeFilter" onchange="filterContacts()" class="form-input" style="width:auto;padding:8px 12px;">
          <option value="">All Time</option>
          <option value="new">New (≤7 days)</option>
          <option value="old">Old (&gt;7 days)</option>
        </select>
      </div>
    </div>

    <!-- NEW CONTACTS (within 7 days) -->
    <div class="mb-5">
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#dbeafe;color:#1d4ed8;">
          <i class="fas fa-envelope-open-text"></i> New Messages <span id="newContactCount" class="ml-1 bg-blue-600 text-white rounded-full px-2 py-0.5 text-xs">0</span>
        </span>
        <span class="text-xs text-gray-400">Received within the last 7 days</span>
      </div>
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div id="newContactsTableWrap" class="overflow-x-auto"></div>
      </div>
    </div>

    <!-- ALL / OLDER CONTACTS -->
    <div>
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold" style="background:#f3f4f6;color:#374151;">
          <i class="fas fa-envelope"></i> Old Messages <span id="oldContactCount" class="ml-1 bg-gray-500 text-white rounded-full px-2 py-0.5 text-xs">0</span>
        </span>
        <span class="text-xs text-gray-400">Messages older than 7 days</span>
      </div>
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div id="contactsTableWrap" class="overflow-x-auto"></div>
      </div>
    </div>
  </div>

  <!-- REPLY MODAL -->
  <div id="replyModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 flex flex-col" style="max-height:90vh;">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
        <h2 class="text-lg font-bold text-gray-800"><i class="fas fa-reply mr-2" style="color:var(--color-primary);"></i>Reply to Message</h2>
        <button onclick="closeReplyModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
      </div>
      <div class="overflow-y-auto px-6 py-5 flex-1">
        <p id="replyMeta" class="text-xs text-gray-400 mb-3"></p>
        <div class="bg-gray-50 rounded-xl p-4 mb-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Original Message</p>
          <p id="replyOriginal" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
        </div>
        <!-- Show existing reply if any -->
        <div id="existingReplyWrap" style="display:none;" class="bg-blue-50 rounded-xl p-4 mb-5">
          <p class="text-xs font-semibold text-blue-400 uppercase tracking-wide mb-1">Previous Reply</p>
          <p id="existingReplyText" class="text-sm text-blue-700 whitespace-pre-wrap"></p>
        </div>
        <label class="form-label">Your Reply</label>
        <textarea id="replyText" class="form-input" rows="5" style="resize:vertical;" placeholder="Type your reply here…"></textarea>
      </div>
      <div class="flex gap-3 px-6 py-4 border-t border-gray-100 flex-shrink-0">
        <button onclick="submitReply()" class="btn-send-reply px-6 py-2 rounded-lg text-white font-semibold transition hover:opacity-90" style="background:var(--color-primary);">
          <i class="fas fa-paper-plane mr-1"></i> Send Reply
        </button>
        <button onclick="closeReplyModal()" class="px-6 py-2 rounded-lg font-semibold transition hover:bg-gray-100" style="background:#f3f4f6;color:#374151;">Cancel</button>
      </div>
    </div>
  </div>

</div>

<!-- TOAST -->
<div id="toast"></div>

<!-- EDIT PRODUCT MODAL -->
<div id="editModal" class="fixed inset-0 z-50 items-center justify-center" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[92vh] flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
      <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-pen mr-2" style="color:var(--color-primary);"></i>Edit Product</h2>
      <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
    </div>

    <!-- Scrollable body -->
    <div class="overflow-y-auto px-6 py-5 flex-1">
      <input type="hidden" id="editId">

      <!-- Image -->
      <div class="mb-4">
        <label class="form-label">Product Images</label>
        <div id="editCurrentImages" class="flex flex-wrap gap-2 mb-2"></div>
        <input type="file" id="editImgInput" accept="image/*" multiple style="display:none;">
        <div id="editDropZone" style="border:2px dashed #cbd5e1;border-radius:14px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s;">
          <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-1 block"></i>
          <p class="text-gray-500 text-xs">Click or drag to upload new images (replaces existing)</p>
        </div>
        <div id="editImgPreview" class="flex flex-wrap gap-2 mt-2"></div>
      </div>

      <!-- Name + Category -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="form-label">Product Name *</label>
          <input class="form-input" id="editName" placeholder="e.g. 4MP Smart Camera">
        </div>
        <div>
          <label class="form-label">Category</label>
          <select class="form-input" id="editCategory">
            <option>Smart CCTV Camera</option>
            <option>Smart Fire Alarm</option>
            <option>Accessories</option>
            <option>Other</option>
          </select>
        </div>
      </div>

      <!-- Description -->
      <div class="mb-4">
        <label class="form-label">Description *</label>
        <textarea class="form-input" id="editDesc" rows="3" style="resize:vertical;" placeholder="Product description…"></textarea>
      </div>

      <!-- Specifications -->
      <div class="mb-4">
        <label class="form-label">Specifications</label>
        <textarea class="form-input" id="editSpecs" rows="2" style="resize:vertical;" placeholder="e.g. Resolution: 4MP, Night Vision: 30m"></textarea>
      </div>

      <!-- Price Variants -->
      <div class="mb-4">
        <div class="flex items-center justify-between mb-2">
          <label class="form-label" style="margin:0;">Prices / Variants *</label>
          <button id="editAddPriceBtn" class="text-sm px-3 py-1 rounded-lg font-semibold transition" style="background:var(--color-quarternary);color:var(--color-primary);">
            <i class="fas fa-plus mr-1"></i> Add Variant
          </button>
        </div>
        <div id="editPriceTiers"></div>
      </div>

      <!-- Status + Featured -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="form-label">Status</label>
          <select class="form-input" id="editStatus">
            <option>Available</option>
            <option>Out of Stock</option>
            <option>Coming Soon</option>
          </select>
        </div>
        <div>
          <label class="form-label">Featured?</label>
          <select class="form-input" id="editFeatured">
            <option value="0">No</option>
            <option value="1">Yes — show on homepage</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex gap-3 px-6 py-4 border-t border-gray-100 flex-shrink-0">
      <button id="saveEditBtn" class="px-6 py-2 rounded-lg text-white font-semibold transition hover:opacity-90" style="background:var(--color-primary);">
        <i class="fas fa-save mr-1"></i> Save Changes
      </button>
      <button onclick="closeModal()" class="px-6 py-2 rounded-lg font-semibold transition hover:bg-gray-100" style="background:#f3f4f6;color:#374151;">Cancel</button>
    </div>
  </div>
</div>

<!-- VIEW USER MODAL -->
<div id="viewUserModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-xl font-bold text-gray-800">User Details</h2>
      <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
    </div>
    <div id="viewUserBody"></div>
  </div>
</div>

<script src="../js/forAdmin.js"></script>
<script>
// ════════════ INVENTORY ════════════
let _inventoryData = [];

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.sidebar-link[data-panel]').forEach(link => {
    link.addEventListener('click', () => {
      if (link.dataset.panel === 'inventory') loadInventory();
    });
  });
});

async function loadInventory() {
  const wrap = document.getElementById('inventoryTableWrap');
  if (!wrap) return;
  wrap.innerHTML = '<div class="text-center py-12 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i><p class="text-sm">Loading inventory…</p></div>';
  try {
    const res = await fetch('../backend/get_products.php');
    _inventoryData = await res.json();
    renderInventoryStats(_inventoryData);
    renderInventoryTable(_inventoryData);
  } catch(e) {
    wrap.innerHTML = '<p class="text-red-400 text-center py-8 text-sm">Could not load inventory.</p>';
  }
}

function renderInventoryStats(data) {
  let totalVariants = 0, inStock = 0, lowStock = 0, outStock = 0;
  data.forEach(p => {
    (p.prices || []).forEach(pr => {
      totalVariants++;
      const s = parseInt(pr.stock) || 0;
      if (s === 0) outStock++;
      else if (s <= 5) lowStock++;
      else inStock++;
    });
  });
  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  set('invTotalVariants', totalVariants);
  set('invInStock', inStock);
  set('invLowStock', lowStock);
  set('invOutStock', outStock);
}

function renderInventoryTable(data) {
  const wrap = document.getElementById('inventoryTableWrap');
  if (!data.length) { wrap.innerHTML = '<p class="text-gray-400 text-center py-12 text-sm">No products found.</p>'; return; }
  let rows = '';
  data.forEach(p => {
    (p.prices || []).forEach((pr, idx) => {
      const stock = parseInt(pr.stock) || 0;
      let badge;
      if (stock === 0) badge = '<span class="badge badge-cancelled">Out of Stock</span>';
      else if (stock <= 5) badge = '<span class="badge badge-pending">Low Stock</span>';
      else badge = '<span class="badge badge-verified">In Stock</span>';
      const img = p.images?.[0]
        ? `<img src="${p.images[0]}" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">`
        : `<div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:#cbd5e1;font-size:.75rem;"></i></div>`;
      rows += `<tr>
        <td><div class="flex items-center gap-2">${img}<span class="font-semibold text-gray-700">${p.name}</span></div></td>
        <td class="text-gray-500 text-sm">${p.category}</td>
        <td class="text-gray-600">${pr.label || 'Standard'}</td>
        <td><div class="flex items-center gap-2"><span class="font-bold text-gray-800">${stock}</span>${badge}</div></td>
        <td class="font-semibold" style="color:var(--color-primary);">&#8369;${parseFloat(pr.price||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
        <td>
          <button onclick="openStockModal(${p.id},${idx},'${p.name.replace(/'/g,"\\'")}','${(pr.label||'Standard').replace(/'/g,"\\'")}',${stock})"
            class="text-xs px-3 py-1.5 rounded-lg font-semibold flex items-center gap-1"
            style="background:#eff6ff;color:#2563eb;">
            <i class="fas fa-pen"></i> Edit
          </button>
        </td>
      </tr>`;
    });
  });
  wrap.innerHTML = `<table class="admin-table">
    <thead><tr><th>Product</th><th>Category</th><th>Variant</th><th>Stock</th><th>Price</th><th>Action</th></tr></thead>
    <tbody>${rows}</tbody>
  </table>`;
}

function filterInventory() {
  const q = (document.getElementById('invSearch')?.value || '').toLowerCase();
  const filter = document.getElementById('invStockFilter')?.value || '';
  const filtered = _inventoryData.filter(p => {
    const matchQ = p.name.toLowerCase().includes(q) || (p.category||'').toLowerCase().includes(q);
    if (!matchQ) return false;
    if (!filter) return true;
    return (p.prices || []).some(pr => {
      const s = parseInt(pr.stock) || 0;
      if (filter === 'out') return s === 0;
      if (filter === 'low') return s > 0 && s <= 5;
      if (filter === 'ok') return s > 5;
      return true;
    });
  });
  renderInventoryTable(filtered);
}

function openStockModal(productId, variantIdx, productName, variantLabel, currentStock) {
  document.getElementById('stockModalProductId').value = productId;
  document.getElementById('stockModalVariantIdx').value = variantIdx;
  document.getElementById('stockModalProduct').textContent = productName;
  document.getElementById('stockModalVariant').textContent = 'Variant: ' + variantLabel;
  document.getElementById('stockModalQty').value = currentStock;
  document.getElementById('stockModal').classList.remove('hidden');
  setTimeout(() => document.getElementById('stockModalQty').focus(), 100);
}

function closeStockModal() {
  document.getElementById('stockModal').classList.add('hidden');
}

async function saveStock() {
  const productId = parseInt(document.getElementById('stockModalProductId').value);
  const variantIdx = parseInt(document.getElementById('stockModalVariantIdx').value);
  const newStock = parseInt(document.getElementById('stockModalQty').value);
  if (isNaN(newStock) || newStock < 0) { toast('Enter a valid stock amount.', true); return; }
  const product = _inventoryData.find(p => p.id === productId);
  if (!product) return;
  const prices = [...(product.prices || [])];
  prices[variantIdx] = { ...prices[variantIdx], stock: newStock };
  try {
    const res = await fetch('../backend/update_stock.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId, prices }),
    });
    const data = await res.json();
    if (data.success) { toast('Stock updated!'); closeStockModal(); loadInventory(); }
    else { toast(data.message || 'Failed to update stock.', true); }
  } catch(e) { toast('Cannot reach server.', true); }
}
</script>

<script>
// ════════════════════════════════════════════════════════
//  USERS — split into New (≤7 days) and Older (>7 days)
// ════════════════════════════════════════════════════════
const SEVEN_DAYS = 7 * 24 * 60 * 60 * 1000;

function isNew(dateStr) {
  if (!dateStr) return false;
  const normalized = String(dateStr).replace(' ', 'T');
  const ms = new Date(normalized).getTime();
  if (isNaN(ms)) return false;
  const diff = Date.now() - ms;
  return diff >= 0 && diff <= SEVEN_DAYS;
}

function buildUserRow(u, i) {
  const initials  = (u.username || 'U').slice(0, 2).toUpperCase();
  const isBanned  = u.is_banned == 1;
  const isAdmin   = u.role === 'admin';
  const isVerified= u.is_verified == 1;

  const statusBadge = isBanned
    ? `<span class="badge badge-cancelled">Banned</span>`
    : isVerified
      ? `<span class="badge badge-verified">Verified</span>`
      : `<span class="badge badge-pending">Pending</span>`;

  const statusSelect = `<select onchange="updateUserStatus(${u.id}, this.value)"
    class="form-input mt-1" style="width:auto;padding:4px 8px;font-size:0.78rem;">
    <option value="verified" ${isVerified && !isBanned ? 'selected':''}>Verified</option>
    <option value="pending"  ${!isVerified && !isBanned ? 'selected':''}>Pending</option>
    <option value="banned"   ${isBanned ? 'selected':''}>Banned</option>
  </select>`;

  const roleBadge = isAdmin
    ? `<span class="badge" style="background:#ede9fe;color:#5b21b6;"><i class="fas fa-shield-halved mr-1"></i>Admin</span>`
    : `<span class="badge" style="background:#f3f4f6;color:#6b7280;">User</span>`;

  const adminBtn = isAdmin
    ? `<button onclick="updateUserRole(${u.id},'remove_admin')" class="text-xs px-2 py-1 rounded-lg font-semibold" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-shield-halved"></i> Remove Admin</button>`
    : `<button onclick="updateUserRole(${u.id},'make_admin')" class="text-xs px-2 py-1 rounded-lg font-semibold" style="background:#ede9fe;color:#5b21b6;"><i class="fas fa-shield-halved"></i> Make Admin</button>`;

  return `<tr class="${isBanned ? 'opacity-60' : ''}">
    <td class="text-gray-400 text-xs">${i + 1}</td>
    <td>
      <div class="flex items-center gap-2">
        <div class="user-avatar">${initials}</div>
        <div>
          <span class="font-semibold text-gray-700">${u.username}</span>
          ${isNew(u.created_at) ? '<span class="ml-1 text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:#dcfce7;color:#15803d;">NEW</span>' : ''}
        </div>
      </div>
    </td>
    <td class="text-gray-500 text-sm">${u.email}</td>
    <td><div>${statusBadge}</div>${statusSelect}</td>
    <td>${roleBadge}</td>
    <td class="text-gray-500 text-xs">${new Date(u.created_at).toLocaleDateString('en-PH')}</td>
    <td><div class="flex flex-col gap-1">${adminBtn}</div></td>
  </tr>`;
}

function buildUserTable(users) {
  if (!users.length) return '<p class="text-center text-gray-400 py-8 text-sm"><i class="fas fa-inbox block text-2xl mb-2"></i>No users here.</p>';
  return `<table class="admin-table">
    <thead><tr><th>#</th><th>User</th><th>Email</th><th>Status</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>${users.map((u, i) => buildUserRow(u, i)).join('')}</tbody>
  </table>`;
}

// Override renderUsersTable from forAdmin.js
function renderUsersTable(users) {
  const newUsers   = users.filter(u => isNew(u.created_at));
  const oldUsers   = users.filter(u => !isNew(u.created_at));
  const adminUsers = users.filter(u => u.role === 'admin');

  const newWrap   = document.getElementById('newUsersTableWrap');
  const oldWrap   = document.getElementById('usersTableWrap');
  const adminWrap = document.getElementById('adminUsersTableWrap');
  const newCount   = document.getElementById('newUserCount');
  const oldCount   = document.getElementById('oldUserCount');
  const adminCount = document.getElementById('adminUserCount');

  if (newWrap)   newWrap.innerHTML   = buildUserTable(newUsers);
  if (oldWrap)   oldWrap.innerHTML   = buildUserTable(oldUsers);
  if (adminWrap) adminWrap.innerHTML = buildUserTable(adminUsers);
  if (newCount)   newCount.textContent   = newUsers.length;
  if (oldCount)   oldCount.textContent   = oldUsers.length;
  if (adminCount) adminCount.textContent = adminUsers.length;
}


// Override updateUserStatus and updateUserRole (in case forAdmin.js version is old)
async function updateUserStatus(userId, value) {
  let action;
  if (value === 'verified') action = 'verify';
  else if (value === 'pending') action = 'unverify';
  else if (value === 'banned') action = 'ban';
  else return;
  try {
    const res  = await fetch('../backend/update_user.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: userId, action }),
    });
    const data = await res.json();
    if (data.success) { toast('User updated!'); loadUsers(); }
    else { toast(data.message || 'Failed.', true); }
  } catch(e) { toast('Cannot reach server.', true); }
}

async function updateUserRole(userId, action) {
  const label = action === 'make_admin' ? 'Make this user an admin?' : 'Remove admin role?';
  if (!confirm(label)) return;
  try {
    const res  = await fetch('../backend/update_user.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: userId, action }),
    });
    const data = await res.json();
    if (data.success) { toast('Role updated!'); loadUsers(); }
    else { toast(data.message || 'Failed.', true); }
  } catch(e) { toast('Cannot reach server.', true); }
}
</script>



<script>
// ═══════════════════════════════════════════════════════════════
//  RECEIVED RECORDS — everything driven by the month/year picker
// ═══════════════════════════════════════════════════════════════
let allReceivedOrders = [];

const MONTHS = ['January','February','March','April','May','June',
                'July','August','September','October','November','December'];

// ── Wire showPanel ──────────────────────────────────────────────
const _origShowPanel = window.showPanel;
window.showPanel = function(name) {
  _origShowPanel(name);
  if (name === 'receivedRecords') loadReceivedOrders();
};

// ── Init pickers to current month/year on page load ─────────────
(function initRecPickers() {
  const now      = new Date();
  const monthSel = document.getElementById('recMonthPicker');
  const yearSel  = document.getElementById('recYearPicker');
  if (!monthSel || !yearSel) return;

  monthSel.value = now.getMonth();

  const thisYear = now.getFullYear();
  for (let y = thisYear + 1; y >= thisYear - 5; y--) {
    const opt = document.createElement('option');
    opt.value = y;
    opt.textContent = y;
    if (y === thisYear) opt.selected = true;
    yearSel.appendChild(opt);
  }
})();

// ── Fetch ALL received orders once, then render by picker ───────
async function loadReceivedOrders() {
  const spinner = col => `<tr><td colspan="${col}" class="text-center py-10 text-gray-400">
    <i class="fas fa-spinner fa-spin mr-2"></i>Loading…</td></tr>`;

  const tb1 = document.getElementById('receivedRecordsBody');
  const tb2 = document.getElementById('receivedMonthBody');
  if (tb1) tb1.innerHTML = spinner(11);
  if (tb2) tb2.innerHTML = spinner(9);

  try {
    const res  = await fetch('../backend/get_orders.php');
    const json = await res.json();
    const data = json.data || json;

    allReceivedOrders = (Array.isArray(data) ? data : []).filter(o =>
      (o.status || '').toLowerCase() === 'received'
    );

    renderByMonth();
  } catch {
    const err = col => `<tr><td colspan="${col}" class="text-center py-8 text-red-400 text-sm">
      Could not load orders.</td></tr>`;
    if (tb1) tb1.innerHTML = err(11);
    if (tb2) tb2.innerHTML = err(9);
  }
}

// ── Called by search input OR picker change ─────────────────────
function filterReceived() { renderByMonth(); }
function renderRecordsTable() { renderByMonth(); }

// ── Core: filter by selected month+year+search, render both tables
function renderByMonth() {
  const monthSel = document.getElementById('recMonthPicker');
  const yearSel  = document.getElementById('recYearPicker');
  if (!monthSel || !yearSel) return;

  const selMonth = parseInt(monthSel.value);
  const selYear  = parseInt(yearSel.value);
  const q        = (document.getElementById('receivedSearch')?.value || '').toLowerCase();

  const label = `${MONTHS[selMonth]} ${selYear}`;

  // Step 1 — filter by month+year (using updated_at if available, else created_at)
  const byMonth = allReceivedOrders.filter(o => {
    const raw = o.updated_at || o.created_at || '';
    const d   = new Date(raw.replace(' ', 'T'));
    return !isNaN(d) && d.getMonth() === selMonth && d.getFullYear() === selYear;
  });

  // Step 2 — apply search on top
  const filtered = byMonth.filter(o =>
    !q ||
    (o.customer_name  || '').toLowerCase().includes(q) ||
    (o.customer_email || '').toLowerCase().includes(q) ||
    (o.order_ref      || '').toLowerCase().includes(q) ||
    (o.customer_phone || '').toLowerCase().includes(q)
  );

  // Sort day-by-day ascending
  const sorted = [...filtered].sort((a, b) =>
    new Date((a.updated_at || a.created_at || '').replace(' ', 'T')) -
    new Date((b.updated_at || b.created_at || '').replace(' ', 'T'))
  );

  // ── Stats ────────────────────────────────────────────────────
  const revenue = filtered.reduce((s, o) => s + parseFloat(o.total || 0), 0);
  const avg     = filtered.length ? revenue / filtered.length : 0;

  const elCount  = document.getElementById('recStatCount');
  const elRev    = document.getElementById('recStatRevenue');
  const elAvg    = document.getElementById('recStatAvg');
  const elAllBadge   = document.getElementById('recAllCount');
  const elMonthBadge = document.getElementById('recMonthCount');
  const elMonthLbl   = document.getElementById('recMonthLabel');
  const statsLabel   = document.getElementById('recStatsLabel');

  if (elCount)      elCount.textContent      = filtered.length;
  if (elRev)        elRev.textContent        = filtered.length ? peso(revenue) : '—';
  if (elAvg)        elAvg.textContent        = filtered.length ? peso(avg) : '—';
  if (elAllBadge)   elAllBadge.textContent   = filtered.length;
  if (elMonthBadge) elMonthBadge.textContent = filtered.length;
  if (elMonthLbl)   elMonthLbl.textContent   = `Records for ${label}`;
  if (statsLabel)   statsLabel.textContent   = label;

  const emptyAll = `<tr><td colspan="11" class="text-center py-14 text-gray-400">
    <i class="fas fa-circle-check text-4xl mb-3 block opacity-20"></i>
    <p class="font-medium">No received orders in ${label}</p>
    <p class="text-xs mt-1">Try a different month or year</p>
  </td></tr>`;

  const emptyMonth = `<tr><td colspan="9" class="text-center py-14 text-gray-400">
    <i class="fas fa-calendar-xmark text-4xl mb-3 block opacity-20"></i>
    <p class="font-medium">No records for ${label}</p>
    <p class="text-xs mt-1">Try a different month or year</p>
  </td></tr>`;

  // ── Received Orders table (all columns) ─────────────────────
  const tb1 = document.getElementById('receivedRecordsBody');
  if (tb1) {
    if (!sorted.length) {
      tb1.innerHTML = emptyAll;
    } else {
      tb1.innerHTML = sorted.map((o, i) => {
        const items    = parseItems(o.items_json || o.items);
        const itemText = items.map(it => `${it.name}${it.qty > 1 ? ' ×'+it.qty : ''}`).join(', ') || '—';
        return `<tr>
          <td class="text-gray-400 text-xs">${i + 1}</td>
          <td class="font-semibold text-gray-700">${escH(o.order_ref || '—')}</td>
          <td class="font-medium text-gray-800">${escH(o.customer_name || '—')}</td>
          <td class="text-gray-500 text-xs">${escH(o.customer_email || '—')}</td>
          <td class="text-gray-500 text-xs">${escH(o.customer_phone || '—')}</td>
          <td class="text-gray-600 text-xs max-w-[160px]" title="${escH(itemText)}">${truncate(itemText, 40)}</td>
          <td class="text-right font-medium">${peso(o.subtotal)}</td>
          <td class="text-right text-gray-500">${peso(o.shipping)}</td>
          <td class="text-right font-bold" style="color:var(--color-primary);">${peso(o.total)}</td>
          <td><span class="badge" style="background:#e0f2fe;color:#0369a1;">${escH(o.payment_method || '—')}</span></td>
          <td class="text-gray-500 text-xs whitespace-nowrap">${fmtDateTime(o.updated_at || o.created_at)}</td>
        </tr>`;
      }).join('');
    }
  }

  // ── Monthly Records table (day-focused) ──────────────────────
  const tb2 = document.getElementById('receivedMonthBody');
  if (tb2) {
    if (!sorted.length) {
      tb2.innerHTML = emptyMonth;
    } else {
      tb2.innerHTML = sorted.map((o, i) => {
        const items    = parseItems(o.items_json || o.items);
        const itemText = items.map(it => `${it.name}${it.qty > 1 ? ' ×'+it.qty : ''}`).join(', ') || '—';
        const d        = new Date((o.updated_at || o.created_at || '').replace(' ', 'T'));
        const dayLabel = d.toLocaleDateString('en-PH', { weekday:'short', month:'short', day:'numeric' });
        return `<tr>
          <td class="text-gray-400 text-xs">${i + 1}</td>
          <td class="font-semibold text-gray-700">${escH(o.order_ref || '—')}</td>
          <td class="font-medium text-gray-800">${escH(o.customer_name || '—')}</td>
          <td class="text-gray-500 text-xs">${escH(o.customer_email || '—')}</td>
          <td class="text-gray-600 text-xs max-w-[150px]" title="${escH(itemText)}">${truncate(itemText, 35)}</td>
          <td class="text-right font-bold" style="color:var(--color-primary);">${peso(o.total)}</td>
          <td><span class="badge" style="background:#e0f2fe;color:#0369a1;">${escH(o.payment_method || '—')}</span></td>
          <td class="text-xs font-semibold text-gray-600 whitespace-nowrap">${dayLabel}</td>
          <td class="text-gray-500 text-xs whitespace-nowrap">${fmtDateTime(o.updated_at || o.created_at)}</td>
        </tr>`;
      }).join('');
    }
  }
}

// ── Export to Excel ──────────────────────────────────────────────
function downloadMonthlyExcel() {
  const MONTHS = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];

  const monthSel = document.getElementById('recMonthPicker');
  const yearSel  = document.getElementById('recYearPicker');
  const selMonth = monthSel ? parseInt(monthSel.value) : new Date().getMonth();
  const selYear  = yearSel  ? parseInt(yearSel.value)  : new Date().getFullYear();
  const label    = `${MONTHS[selMonth]} ${selYear}`;

  // Get the currently filtered+sorted monthly data
  const q = (document.getElementById('receivedSearch')?.value || '').toLowerCase();

  const byMonth = allReceivedOrders.filter(o => {
    const raw = o.updated_at || o.created_at || '';
    const d   = new Date(raw.replace(' ', 'T'));
    return !isNaN(d) && d.getMonth() === selMonth && d.getFullYear() === selYear;
  });

  const filtered = byMonth.filter(o =>
    !q ||
    (o.customer_name  || '').toLowerCase().includes(q) ||
    (o.customer_email || '').toLowerCase().includes(q) ||
    (o.order_ref      || '').toLowerCase().includes(q) ||
    (o.customer_phone || '').toLowerCase().includes(q)
  );

  const sorted = [...filtered].sort((a, b) =>
    new Date((a.updated_at || a.created_at || '').replace(' ', 'T')) -
    new Date((b.updated_at || b.created_at || '').replace(' ', 'T'))
  );

  if (!sorted.length) {
    toast(`No records for ${label} to export.`, true);
    return;
  }

  // Build rows for PHP
  const rows = sorted.map((o, i) => {
    const items    = parseItems(o.items_json || o.items);
    const itemText = items.map(it => `${it.name}${it.qty > 1 ? ' x'+it.qty : ''}`).join(', ') || '—';
    const d        = new Date((o.updated_at || o.created_at || '').replace(' ', 'T'));
    return {
      no:           i + 1,
      order_ref:    o.order_ref      || '—',
      customer:     o.customer_name  || '—',
      email:        o.customer_email || '—',
      phone:        o.customer_phone || '—',
      items:        itemText,
      subtotal:     parseFloat(o.subtotal  || 0),
      shipping:     parseFloat(o.shipping  || 0),
      total:        parseFloat(o.total     || 0),
      payment:      o.payment_method || '—',
      day:          isNaN(d) ? '—' : d.toLocaleDateString('en-PH', {weekday:'short',month:'short',day:'numeric'}),
      received_at:  isNaN(d) ? '—' : d.toLocaleDateString('en-PH', {year:'numeric',month:'short',day:'numeric'})
                                + ' ' + d.toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'})
    };
  });

  // POST to PHP export endpoint
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '../backend/export_received.php';
  form.style.display = 'none';

  const addField = (name, value) => {
    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = name;
    input.value = value;
    form.appendChild(input);
  };

  addField('month_label', label);
  addField('month_num',   selMonth + 1);   // 1-based for PHP
  addField('year',        selYear);
  addField('rows_json',   JSON.stringify(rows));

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

// ── Helpers ──────────────────────────────────────────────────────
function parseItems(raw) {
  if (!raw) return [];
  try { const a = typeof raw === 'string' ? JSON.parse(raw) : raw; return Array.isArray(a) ? a : []; }
  catch { return []; }
}
function fmtDateTime(s) {
  if (!s) return '—';
  const d = new Date(String(s).replace(' ', 'T'));
  if (isNaN(d)) return '—';
  return d.toLocaleDateString('en-PH', {year:'numeric',month:'short',day:'numeric'})
    + ' ' + d.toLocaleTimeString('en-PH', {hour:'2-digit',minute:'2-digit'});
}
function truncate(s, n) { return s.length > n ? s.slice(0, n) + '…' : s; }
function escH(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ══════════════════════════════════════════════════════
//  CANCELLED ORDERS PANEL
// ══════════════════════════════════════════════════════
let cancelledOrders = [];

async function loadCancelledOrders() {
  try {
    const res  = await fetch('../backend/get_cancelled_orders.php');
    const data = await res.json();
    if (!data.success) { cancelledTableError('Server error: ' + (data.message||'')); return; }
    cancelledOrders = data.data || [];
    renderCancelledTable(cancelledOrders);
    updateCancelledStats(cancelledOrders);
  } catch(e) { cancelledTableError('Cannot reach server.'); }
}

function updateCancelledStats(orders) {
  document.getElementById('cancelledCount').textContent    = orders.length;
  const revenue = orders.reduce((s, o) => s + parseFloat(o.total || 0), 0);
  document.getElementById('cancelledRevenue').textContent  = '₱' + revenue.toLocaleString('en-PH', {minimumFractionDigits:2});
  const unique = new Set(orders.map(o => o.customer_email || o.id)).size;
  document.getElementById('cancelledCustomers').textContent = unique;
}

function renderCancelledTable(orders) {
  const tbody = document.getElementById('cancelledTableBody');
  if (!orders.length) {
    tbody.innerHTML = `<tr><td colspan="12" class="text-center py-10 text-gray-400">
      <i class="fas fa-ban mr-2"></i>No cancelled orders found.</td></tr>`;
    return;
  }
  tbody.innerHTML = orders.map(o => {
    const items = (o.items || []).map(i => `${escH(i.name)} x${i.qty||1}`).join(', ');
    const date  = o.created_at ? new Date(o.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'}) : '—';
    return `<tr>
      <td>${o.id}</td>
      <td><span class="font-bold" style="color:var(--color-primary);">${escH(o.order_ref)}</span></td>
      <td>${escH(o.customer_name||'—')}</td>
      <td>${escH(o.username||'Guest')}</td>
      <td>${escH(o.customer_email||'—')}</td>
      <td>${escH(o.customer_phone||'—')}</td>
      <td style="max-width:220px;white-space:normal;font-size:.78rem;">${escH(items)||'—'}</td>
      <td style="text-align:right;font-weight:700;color:#dc2626;">₱${parseFloat(o.total||0).toFixed(2)}</td>
      <td>${escH(o.payment_method||'—')}</td>
      <td style="max-width:180px;white-space:normal;font-size:.78rem;">${escH(o.address||'—')}</td>
      <td style="white-space:nowrap;">${date}</td>
      <td>
        <button onclick="deleteSingleCancelled(${o.id})"
          class="px-3 py-1 rounded-lg text-xs font-bold text-white"
          style="background:#ef4444;" title="Delete this order permanently">
          <i class="fas fa-trash-can"></i>
        </button>
      </td>
    </tr>`;
  }).join('');
}

function filterCancelled() {
  const q = document.getElementById('cancelledSearch').value.toLowerCase();
  const filtered = cancelledOrders.filter(o =>
    (o.customer_name||'').toLowerCase().includes(q) ||
    (o.order_ref||'').toLowerCase().includes(q) ||
    (o.customer_email||'').toLowerCase().includes(q) ||
    (o.username||'').toLowerCase().includes(q)
  );
  renderCancelledTable(filtered);
}

function cancelledTableError(msg) {
  document.getElementById('cancelledTableBody').innerHTML =
    `<tr><td colspan="12" class="text-center py-10 text-red-400">${msg}</td></tr>`;
}

// Delete single row
async function deleteSingleCancelled(id) {
  if (!confirm('Permanently delete this cancelled order?')) return;
  await deleteCancelledIds([id]);
}

// Delete all
document.getElementById('clearAllCancelledBtn').addEventListener('click', async function() {
  if (!cancelledOrders.length) { alert('No cancelled orders to delete.'); return; }
  if (!confirm(`Permanently delete all ${cancelledOrders.length} cancelled order(s)? This cannot be undone.`)) return;
  const ids = cancelledOrders.map(o => o.id);
  await deleteCancelledIds(ids);
});

async function deleteCancelledIds(ids) {
  try {
    const res  = await fetch('../backend/delete_cancelled_admin.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ ids })
    });
    const data = await res.json();
    if (data.success) {
      showAdminToast(`${data.deleted} order(s) deleted.`);
      await loadCancelledOrders();
    } else {
      showAdminToast('Error: ' + (data.message||'Delete failed.'));
    }
  } catch(e) { showAdminToast('Network error.'); }
}

// Load when panel is opened
document.querySelectorAll('.sidebar-link[data-panel]').forEach(link => {
  if (link.dataset.panel === 'cancelledOrders') {
    link.addEventListener('click', loadCancelledOrders);
  }
});

function showAdminToast(msg) {
  // reuse existing toast if present, else fallback to alert
  const t = document.getElementById('toast');
  if (t) {
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
  } else { alert(msg); }
}

</script>
</body>
</html>