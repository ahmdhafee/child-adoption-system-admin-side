<?php
require_once '../officer_auth.php';
if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Clients | Admin</title>

  <!-- Admin styles -->
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="favlogo.png" type="logo">

  <style>
    * {margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}

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
    .b-app{background:#2980b9;}
    .filters{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    .filters label{display:block;font-size:12px;color:#555;margin-bottom:5px;}
    .filters input,.filters select{padding:10px;border:1px solid #ddd;border-radius:10px;min-width:220px;}
  </style>
</head>

<body>
<div class="app-container">

  <!-- Sidebar (Admin) -->
  <aside class="sidebar">
  <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-hands-helping"></i>
                <div>
                    <div style="font-size: 1.3rem;">Family Bridge</div>
                    <div >Admin Panel</div>
                </div>
            </div>
        </div>

    <nav class="sidebar-nav">
    <a href="index.php" class="nav-item" >
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>

            <a href="children-management.php" class="nav-item">
                <i class="fas fa-child"></i>
                Children Management
            </a>

            <a href="inquiries.php" class="nav-item">
                <i class="fas fa-question-circle"></i>
                Inquiries
               
            </a>

            <a href="clients.php" class="nav-item active">
                <i class="fas fa-users"></i>
                Clients
            </a>

            <a href="appointments.php" class="nav-item">
                <i class="fas fa-calendar-check"></i>
                Appointments
               
            </a>
            <a href="documents_review.php" class="nav-item">
                <i class="fas fa-file-alt"></i> 
                <span>Document Review</span>
            </a>
            
    </nav>

    
  </aside>

  <!-- Main -->
  <main class="main-content">

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
                    <div class="admin-avatar">AD</div>
                    <div class="admin-info">
                        <!-- keep class admin-info -->
                      
                        <p>System Administrator</p>
                    </div>
                </div>

                <!-- KEEP logout button style + id if you want a second one:
                     Your code had a second logout button with same ID -> that's invalid.
                     So we keep only sidebar logout, and keep classes unchanged.
                -->
                <a class="logout-btn" href="../officer_logout.php" style="text-decoration:none;">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
    </header>

    <!-- Content -->
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

function badgeApp(s){
  return 'b-app';
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
      <td><span class="badge ${badgeApp(c.application_status)}">${esc(c.application_status || '-')}</span></td>
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

// logout
document.getElementById('logoutBtn').addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});
</script>

</body>
</html>
