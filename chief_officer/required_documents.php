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
  <title>Required Documents | Chief Officer</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="../favlogo.png" type="logo">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
    .wrap{padding:20px;}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08);padding:15px;margin-bottom:15px;}
    table{width:100%;border-collapse:collapse;}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:14px;vertical-align:top;}
    th{background:#f3f5fa;}
    .btn{padding:8px 10px;border-radius:8px;border:none;cursor:pointer;font-weight:600}
    .btn-primary{background:#3498db;color:#fff;}
    .btn-outline{background:#fff;border:1px solid #ddd;}
    .btn-danger{background:#e74c3c;color:#fff;}
    .btn-success{background:#27ae60;color:#fff;}
    .badge{padding:4px 10px;border-radius:999px;font-size:12px;color:#fff;display:inline-block;}
    .b-on{background:#27ae60;}
    .b-off{background:#7f8c8d;}
    .b-req{background:#8e44ad;}
    .b-opt{background:#16a085;}

    .filters{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    .filters label{display:block;font-size:12px;color:#555;margin-bottom:5px;}
    .filters input,.filters select{padding:10px;border:1px solid #ddd;border-radius:10px;min-width:220px;}
    .row-actions{display:flex;gap:8px;flex-wrap:wrap;}

    /* modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:9999;}
    .modal{background:#fff;border-radius:12px;max-width:820px;width:95%;box-shadow:0 10px 30px rgba(0,0,0,.2);overflow:hidden;}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #eee;}
    .modal-body{padding:16px;}
    .modal-footer{padding:14px 16px;border-top:1px solid #eee;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .form-group{display:flex;flex-direction:column;gap:6px;}
    .form-group label{font-size:12px;color:#555;font-weight:700;}
    .form-group input,.form-group select,.form-group textarea{
      padding:10px;border:1px solid #ddd;border-radius:10px;
    }
    textarea{min-height:90px;resize:vertical;}
    .hint{font-size:12px;color:#777;}
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

    

    <nav class="sidebar-nav">
      <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <a href="users.php" class="nav-item"><i class="fas fa-users"></i><span>User Management</span></a>
      <a href="children-management.php" class="nav-item"><i class="fas fa-child"></i><span>Children Management</span></a>
      <a href="clients.php" class="nav-item"><i class="fas fa-user-friends"></i><span>Clients</span></a>
      <a href="appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>Appointments</span></a>
      <a href="Inquires.php" class="nav-item"><i class="fas fa-question-circle"></i><span>Inquiries</span></a>
      <a href="guidelines.php" class="nav-item"><i class="fas fa-book"></i><span>Guidelines</span></a>
      <a href="institutes.php" class="nav-item"><i class="fas fa-book"></i><span>Institute</span></a>


      <a href="required_documents.php" class="nav-item active">
        <i class="fas fa-file-upload"></i><span>Required Documents</span>
      </a>
    </nav>

    <div class="logout-section">
      <button class="logout-btn" id="logoutBtn">
        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main -->
  <main class="main-content">

    <header class="header">
      <div class="header-left">
        <button class="menu-toggle" id="menuToggle" type="button"><i class="fas fa-bars"></i></button>
        <div class="page-title">
          <h1>Required Documents</h1>
          <p>Chief can edit which documents are mandatory for approval</p>
        </div>
      </div>

      <div class="header-right">
        <div class="header-actions">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search requirement name / category...">
          </div>
          <button class="btn btn-primary" id="addBtn" type="button">
            <i class="fas fa-plus"></i> Add Requirement
          </button>
          <button class="btn btn-outline" id="refreshBtn" type="button">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
        </div>
      </div>
    </header>

    <div class="wrap">

      <div class="card">
        <div class="filters">
          <div>
            <label>Category</label>
            <select id="categoryFilter">
              <option value="all">All</option>
              <option value="identity">Identity</option>
              <option value="legal">Legal</option>
              <option value="medical">Medical</option>
              <option value="financial">Financial</option>
              <option value="home-study">Home Study</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label>Active</label>
            <select id="activeFilter">
              <option value="all">All</option>
              <option value="1">Active only</option>
              <option value="0">Inactive only</option>
            </select>
          </div>

          <div>
            <label>Required</label>
            <select id="requiredFilter">
              <option value="all">All</option>
              <option value="1">Required only</option>
              <option value="0">Optional only</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <table>
          <thead>
            <tr>
              <th style="width:70px;">Order</th>
              <th>Requirement</th>
              <th>Category</th>
              <th>Rules</th>
              <th>Active</th>
              <th>Required</th>
              <th style="width:260px;">Actions</th>
            </tr>
          </thead>
          <tbody id="reqTbody">
            <tr><td colspan="7">Loading...</td></tr>
          </tbody>
        </table>

        <div class="hint" style="margin-top:10px;">
          Tip: Use ⬆️⬇️ to change sort order. If a doc is <b>inactive</b>, clients won’t be asked for it.
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="reqModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modalTitle">Add Requirement</h3>
      <button class="btn btn-outline" id="closeModalBtn" type="button">&times;</button>
    </div>

    <div class="modal-body">
      <div class="grid">
        <div class="form-group">
          <label>Requirement Name *</label>
          <input type="text" id="requirement_name" placeholder="e.g., Marriage Certificate">
        </div>

        <div class="form-group">
          <label>Category *</label>
          <select id="category">
            <option value="identity">identity</option>
            <option value="legal">legal</option>
            <option value="medical">medical</option>
            <option value="financial">financial</option>
            <option value="home-study">home-study</option>
            <option value="other">other</option>
          </select>
        </div>

        <div class="form-group">
          <label>Max Size (MB) *</label>
          <input type="number" id="max_size_mb" min="1" value="10">
        </div>

        <div class="form-group">
          <label>Allowed Formats *</label>
          <input type="text" id="allowed_formats" placeholder="pdf,jpg,jpeg,png">
        </div>

        <div class="form-group">
          <label>Is Active</label>
          <select id="is_active">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>

        <div class="form-group">
          <label>Is Required</label>
          <select id="is_required">
            <option value="1">Required</option>
            <option value="0">Optional</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-top:12px;">
        <label>Description</label>
        <textarea id="description" placeholder="Explain what the client must upload (Sri Lanka adoption)…"></textarea>
      </div>

      <div class="hint">
        allowed_formats example: <b>pdf,jpg,jpeg,png</b> (comma-separated)
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-outline" id="cancelBtn" type="button">Cancel</button>
      <button class="btn btn-primary" id="saveBtn" type="button">
        <i class="fas fa-save"></i> Save
      </button>
    </div>
  </div>
</div>

<script>
const API_URL = "required_documents_api.php";

const tbody = document.getElementById('reqTbody');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const activeFilter = document.getElementById('activeFilter');
const requiredFilter = document.getElementById('requiredFilter');
const refreshBtn = document.getElementById('refreshBtn');
const addBtn = document.getElementById('addBtn');

const reqModal = document.getElementById('reqModal');
const modalTitle = document.getElementById('modalTitle');
const closeModalBtn = document.getElementById('closeModalBtn');
const cancelBtn = document.getElementById('cancelBtn');
const saveBtn = document.getElementById('saveBtn');

const requirement_name = document.getElementById('requirement_name');
const category = document.getElementById('category');
const description = document.getElementById('description');
const is_required = document.getElementById('is_required');
const max_size_mb = document.getElementById('max_size_mb');
const allowed_formats = document.getElementById('allowed_formats');
const is_active = document.getElementById('is_active');

let allReq = [];
let editingId = null;

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function openModal(){
  reqModal.style.display = 'flex';
}
function closeModal(){
  reqModal.style.display = 'none';
  editingId = null;
  modalTitle.textContent = 'Add Requirement';
  requirement_name.value = '';
  category.value = 'identity';
  description.value = '';
  is_required.value = '1';
  max_size_mb.value = '10';
  allowed_formats.value = 'pdf,jpg,jpeg,png';
  is_active.value = '1';
}

function badgeActive(v){
  return v == 1 ? '<span class="badge b-on">Active</span>' : '<span class="badge b-off">Inactive</span>';
}
function badgeRequired(v){
  return v == 1 ? '<span class="badge b-req">Required</span>' : '<span class="badge b-opt">Optional</span>';
}

function render(list){
  tbody.innerHTML = list.map(r => `
    <tr>
      <td>
        <div style="display:flex;gap:8px;align-items:center;">
          <strong>${esc(r.sort_order ?? 0)}</strong>
          <div style="display:flex;flex-direction:column;gap:6px;">
            <button class="btn btn-outline" data-action="up" data-id="${esc(r.id)}" title="Move up">⬆️</button>
            <button class="btn btn-outline" data-action="down" data-id="${esc(r.id)}" title="Move down">⬇️</button>
          </div>
        </div>
      </td>

      <td>
        <strong>${esc(r.requirement_name)}</strong><br>
        <small style="color:#666;">${esc(r.description || '')}</small>
      </td>

      <td>${esc(r.category || '-')}</td>

      <td>
        <div><small class="hint">Max</small> <b>${esc(r.max_size_mb ?? 10)}MB</b></div>
        <div><small class="hint">Formats</small> ${esc(r.allowed_formats || '-')}</div>
      </td>

      <td>${badgeActive(r.is_active)}</td>
      <td>${badgeRequired(r.is_required)}</td>

      <td>
        <div class="row-actions">
          <button class="btn btn-outline" data-action="edit" data-id="${esc(r.id)}">Edit</button>
          <button class="btn ${r.is_active==1 ? 'btn-danger' : 'btn-success'}" data-action="toggle" data-id="${esc(r.id)}">
            ${r.is_active==1 ? 'Deactivate' : 'Activate'}
          </button>
          <button class="btn btn-danger" data-action="delete" data-id="${esc(r.id)}">Delete</button>
        </div>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="7">No requirements found</td></tr>`;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const cat = categoryFilter.value;
  const act = activeFilter.value;
  const req = requiredFilter.value;

  let list = allReq.filter(r => {
    const hay = (r.requirement_name + ' ' + (r.category||'') + ' ' + (r.description||'')).toLowerCase();
    if(q && !hay.includes(q)) return false;
    if(cat !== 'all' && (r.category||'') !== cat) return false;
    if(act !== 'all' && String(r.is_active ?? 1) !== String(act)) return false;
    if(req !== 'all' && String(r.is_required ?? 1) !== String(req)) return false;
    return true;
  });

  // always show in sort order
  list.sort((a,b)=> (Number(a.sort_order||0) - Number(b.sort_order||0)));

  render(list);
}

async function loadRequirements(){
  const res = await fetch(`${API_URL}?action=list`);
  const data = await res.json();
  if(!data.success){
    tbody.innerHTML = `<tr><td colspan="7">${esc(data.message || 'Failed')}</td></tr>`;
    return;
  }
  allReq = data.required_documents || [];
  applyFilters();
}

async function saveRequirement(){
  const name = requirement_name.value.trim();
  const cat = category.value.trim();
  const desc = description.value.trim();
  const reqv = is_required.value;
  const max = max_size_mb.value;
  const fmt = allowed_formats.value.trim();
  const act = is_active.value;

  if(!name || !cat || !max || !fmt){
    alert('Please fill required fields');
    return;
  }

  const form = new FormData();
  form.append('action', editingId ? 'update' : 'create');
  if(editingId) form.append('id', editingId);

  form.append('requirement_name', name);
  form.append('category', cat);
  form.append('description', desc);
  form.append('is_required', reqv);
  form.append('max_size_mb', max);
  form.append('allowed_formats', fmt);
  form.append('is_active', act);

  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Saved' : 'Failed'));
  if(data.success){
    closeModal();
    loadRequirements();
  }
}

async function toggleActive(id){
  const form = new FormData();
  form.append('action','toggle_active');
  form.append('id', id);

  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Updated' : 'Failed'));
  if(data.success) loadRequirements();
}

async function deleteReq(id){
  if(!confirm('Delete this requirement?')) return;
  const form = new FormData();
  form.append('action','delete');
  form.append('id', id);
  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Deleted' : 'Failed'));
  if(data.success) loadRequirements();
}

async function move(id, dir){
  const form = new FormData();
  form.append('action','reorder');
  form.append('id', id);
  form.append('dir', dir); // up/down
  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await res.json();
  if(!data.success) alert(data.message || 'Failed');
  if(data.success) loadRequirements();
}

tbody.addEventListener('click', (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;

  const action = btn.dataset.action;
  const id = btn.dataset.id;

  if(action === 'edit'){
    const r = allReq.find(x => String(x.id) === String(id));
    if(!r) return;
    editingId = id;
    modalTitle.textContent = 'Edit Requirement';
    requirement_name.value = r.requirement_name || '';
    category.value = r.category || 'identity';
    description.value = r.description || '';
    is_required.value = String(r.is_required ?? 1);
    max_size_mb.value = String(r.max_size_mb ?? 10);
    allowed_formats.value = r.allowed_formats || 'pdf,jpg,jpeg,png';
    is_active.value = String(r.is_active ?? 1);
    openModal();
  }

  if(action === 'toggle') toggleActive(id);
  if(action === 'delete') deleteReq(id);
  if(action === 'up') move(id,'up');
  if(action === 'down') move(id,'down');
});

addBtn.addEventListener('click', ()=>{
  editingId = null;
  modalTitle.textContent = 'Add Requirement';
  openModal();
});

closeModalBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);
reqModal.addEventListener('click', (e)=>{ if(e.target === reqModal) closeModal(); });
saveBtn.addEventListener('click', saveRequirement);

searchInput.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', applyFilters);
activeFilter.addEventListener('change', applyFilters);
requiredFilter.addEventListener('change', applyFilters);
refreshBtn.addEventListener('click', loadRequirements);

// logout
document.getElementById('logoutBtn').addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

loadRequirements();
</script>
</body>
</html>
