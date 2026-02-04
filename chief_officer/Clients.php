<?php
require_once '../officer_auth.php';
if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clients | Chief Officer</title>

  <!-- Your existing chief styles -->
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="../favlogo.png" type="logo">

  <style>
    * {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}

    /* Your old clients layout moved inside content */
    .wrap{padding:20px;}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08);padding:15px;margin-bottom:15px;}
    table{width:100%;border-collapse:collapse;}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:14px;}
    th{background:#f3f5fa;}
    .btn{padding:8px 10px;border-radius:8px;border:none;cursor:pointer;font-weight:600}
    .btn-primary{background:#3498db;color:#fff;}
    .btn-outline{background:#fff;border:1px solid #ddd;}
    .btn-danger{background:#e74c3c;color:#fff;}
    .badge{padding:4px 10px;border-radius:999px;font-size:12px;color:#fff;}
    .b-active{background:#27ae60;}
    .b-pending{background:#f39c12;}
    .b-suspended{background:#e74c3c;}
    .b-app-pending{background:#2980b9;}
    .filters{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    .filters label{display:block;font-size:12px;color:#555;margin-bottom:5px;}
    .filters input,.filters select{padding:10px;border:1px solid #ddd;border-radius:10px;min-width:220px;}
  </style>
</head>

<body>
<div class="app-container">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <i class="fas fa-hands-helping"></i>
        <div>
          <div style="font-size:1.3rem;">Family Bridge</div>
          <div class="admin-tag">Chief Officer Portal</div>
        </div>
      </div>
    </div>

    <div class="admin-info">
      <div class="admin-avatar">
        <i class="fas fa-user-shield"></i>
      </div>
      <div class="admin-name"><?php echo htmlspecialchars($_SESSION['officer_name'] ?? 'Chief Officer'); ?></div>
      <div class="admin-role">System Administrator</div>
    </div>

    <nav class="sidebar-nav">
      <a href="index.php" class="nav-item">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
      </a>
      <a href="users.php" class="nav-item">
        <i class="fas fa-users"></i>
        <span>User Management</span>
      </a>
      <a href="children-management.php" class="nav-item">
        <i class="fas fa-child"></i>
        <span>Children Management</span>
      </a>
      <a href="clients.php" class="nav-item active">
        <i class="fas fa-user-friends"></i>
        <span>Clients</span>
      </a>
      <a href="appointments.php" class="nav-item">
        <i class="fas fa-calendar-check"></i>
        <span>Appointments</span>
      </a>
      <a href="Inquires.php" class="nav-item">
        <i class="fas fa-question-circle"></i>
        <span>Inquiries</span>
      </a>
      <a href="guidelines.php" class="nav-item">
        <i class="fas fa-book"></i>
        <span>Guidelines</span>
      </a>
    </nav>

    <div class="logout-section">
      <button class="logout-btn" id="logoutBtn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <!-- Header -->
    <header class="header">
      <div class="header-left">
        <button class="menu-toggle" id="menuToggle" type="button">
          <i class="fas fa-bars"></i>
        </button>
        <div class="page-title">
          <h1>Clients</h1>
          <p>View and manage all registered couples</p>
        </div>
      </div>

      <div class="header-right">
        <div class="admin-profile">
          <div class="admin-avatar-sm">CO</div>
          <div class="admin-info-sm">
            <h4><?php echo htmlspecialchars($_SESSION['officer_name'] ?? 'Chief Officer'); ?></h4>
            <p>Administrator</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Your Existing Clients Page Content -->
    <div class="wrap">
      <div class="card">
        <h2 style="margin-bottom:10px;">Clients Management</h2>
        <div class="filters">
          <div>
            <label>Search (name/email/Reg ID)</label>
            <input id="searchInput" placeholder="Search..." />
          </div>
          <div>
            <label>User Status</label>
            <select id="statusFilter">
              <option value="all">All</option>
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
          <div>
            <label>Application Status</label>
            <select id="appStatusFilter">
              <option value="all">All</option>
              <option value="pending">pending</option>
              <option value="under_review">under_review</option>
              <option value="approved">approved</option>
              <option value="rejected">rejected</option>
            </select>
          </div>
          <button class="btn btn-outline" id="refreshBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
      </div>

      <div class="card">
        <table>
          <thead>
            <tr>
              <th>Client</th>
              <th>Registration ID</th>
              <th>User Status</th>
              <th>Eligibility</th>
              <th>Application Status</th>
              <th>Docs</th>
              <th>Voted</th>
              <th>Appointments</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="clientsTbody">
            <tr><td colspan="9">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<script>
const API_URL = "clients_api.php";
const tbody = document.getElementById('clientsTbody');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const appStatusFilter = document.getElementById('appStatusFilter');
const refreshBtn = document.getElementById('refreshBtn');

let allClients = [];

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function badgeStatus(s){
  if (s === 'active') return 'b-active';
  if (s === 'pending') return 'b-pending';
  return 'b-suspended';
}

function render(list){
  tbody.innerHTML = list.map(c => `
    <tr>
      <td>
        <strong>${esc(c.name)}</strong><br>
        <small>${esc(c.email)}</small><br>
        <small>User ID: ${esc(c.user_id)}</small>
      </td>
      <td>${esc(c.registration_id || '-')}</td>
      <td><span class="badge ${badgeStatus(c.user_status)}">${esc(c.user_status)}</span></td>
      <td>${esc(c.eligibility_score)}%</td>
      <td><span class="badge b-app-pending">${esc(c.application_status || '-')}</span></td>
      <td>${esc(c.docs_total)} total / ${esc(c.docs_approved)} approved</td>
      <td>${c.has_voted ? 'Yes' : 'No'}</td>
      <td>${esc(c.appointment_count)}</td>
      <td>
        <button class="btn btn-outline" data-action="view" data-id="${esc(c.user_id)}">View</button>
        <button class="btn ${c.user_status==='active' ? 'btn-danger' : 'btn-primary'}" data-action="toggle" data-id="${esc(c.user_id)}">
          ${c.user_status==='active' ? 'Suspend' : 'Activate'}
        </button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="9">No clients found</td></tr>`;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const st = statusFilter.value;
  const ast = appStatusFilter.value;

  let list = allClients.filter(c => {
    const hay = (c.name + ' ' + c.email + ' ' + (c.registration_id||'')).toLowerCase();
    if (q && !hay.includes(q)) return false;
    if (st !== 'all' && c.user_status !== st) return false;
    if (ast !== 'all' && (c.application_status||'') !== ast) return false;
    return true;
  });

  render(list);
}

async function loadClients(){
  const res = await fetch(`${API_URL}?action=list`);
  const data = await res.json();
  if (!data.success){
    tbody.innerHTML = `<tr><td colspan="9">${esc(data.message||'Failed')}</td></tr>`;
    return;
  }
  allClients = data.clients || [];
  applyFilters();
}

async function toggleClient(user_id){
  const form = new FormData();
  form.append('action','toggle');
  form.append('user_id', user_id);

  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Updated' : 'Failed'));
  if(data.success) loadClients();
}

tbody.addEventListener('click', (e)=>{
  const btn = e.target.closest('button');
  if (!btn) return;
  const action = btn.dataset.action;
  const id = btn.dataset.id;

  if (action === 'toggle') toggleClient(id);
  if (action === 'view') window.location.href = `client_view.php?user_id=${encodeURIComponent(id)}`;
});

searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
appStatusFilter.addEventListener('change', applyFilters);
refreshBtn.addEventListener('click', loadClients);

loadClients();

// Logout button -> server logout
document.getElementById('logoutBtn').addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});
</script>

</body>
</html>
