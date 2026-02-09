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
  <title>Document Review | Admin</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="shortcut icon" href="../favlogo.png" type="logo">

  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
    .content{padding:20px;}
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.08);padding:15px;margin-bottom:15px;}
    table{width:100%;border-collapse:collapse;}
    th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:14px;vertical-align:top;}
    th{background:#f3f5fa;}
    .btn{padding:8px 10px;border-radius:8px;border:none;cursor:pointer;font-weight:600}
    .btn-primary{background:#3498db;color:#fff;}
    .btn-outline{background:#fff;border:1px solid #ddd;}
    .btn-danger{background:#e74c3c;color:#fff;}
    .btn-success{background:#27ae60;color:#fff;}
    .btn-secondary{background:#6c757d;color:#fff;}

    .badge{padding:4px 10px;border-radius:999px;font-size:12px;color:#fff;display:inline-block;text-transform:capitalize;}
    .b-uploaded{background:#6b7280;}
    .b-pending{background:#f39c12;}
    .b-approved{background:#27ae60;}
    .b-rejected{background:#e74c3c;}

    .filters{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    .filters label{display:block;font-size:12px;color:#555;margin-bottom:5px;}
    .filters input,.filters select{padding:10px;border:1px solid #ddd;border-radius:10px;min-width:220px;}

    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:9999;}
    .modal{background:#fff;border-radius:12px;max-width:900px;width:96%;box-shadow:0 10px 30px rgba(0,0,0,.2);overflow:hidden;}
    .modal-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #eee;}
    .modal-body{padding:16px;}
    .modal-footer{padding:14px 16px;border-top:1px solid #eee;display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .field{background:#f7f8fb;border:1px solid #eee;border-radius:10px;padding:10px;}
    .field small{display:block;color:#666;margin-bottom:4px;}
    .field div{font-weight:600;color:#222;}
    textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;min-height:90px;resize:vertical;}
    .link{color:#3498db;text-decoration:none;}
    .link:hover{text-decoration:underline;}
    .hint{font-size:13px;color:#666;margin-top:8px;}
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
          <div>Admin Portal</div>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
      <a href="children-management.php" class="nav-item"><i class="fas fa-child"></i> <span>Children</span></a>
      <a href="inquiries.php" class="nav-item"><i class="fas fa-question-circle"></i> <span>Inquiries</span></a>
      <a href="clients.php" class="nav-item"><i class="fas fa-users"></i> <span>Clients</span></a>
      <a href="appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i> <span>Appointments</span></a>
      <a href="documents_review.php" class="nav-item active"><i class="fas fa-file-alt"></i> <span>Document Review</span></a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="main-content">
    <header class="header">
      <div class="header-left">
        <button class="menu-toggle" id="menuToggle" type="button"><i class="fas fa-bars"></i></button>
        <div class="page-title">
          <h1>Document Review</h1>
          <p>Select a client and review all uploaded documents</p>
        </div>
      </div>

      <div class="header-right">
        <div class="admin-profile">
          <div class="admin-avatar">AD</div>
          <div class="admin-info">
            <p>System Administrator</p>
          </div>
        </div>

        <a class="logout-btn" href="../officer_logout.php" style="text-decoration:none;">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>

    <div class="content">

      <!-- Filters -->
      <div class="card">
        <div class="filters">

          <div>
            <label>Client</label>
            <select id="clientSelect">
              <option value="">Loading clients...</option>
            </select>
            <div class="hint">Choose a client to load all their documents.</div>
          </div>

          <div>
            <label>Search</label>
            <input id="searchInput" type="text" placeholder="Search requirement / filename..." />
          </div>

          <div>
            <label>Status</label>
            <select id="statusFilter">
              <option value="all">All</option>
              <option value="uploaded">Uploaded</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div>
            <label>Category</label>
            <select id="categoryFilter">
              <option value="all">All</option>
              <option value="identity">identity</option>
              <option value="legal">legal</option>
              <option value="medical">medical</option>
              <option value="financial">financial</option>
              <option value="home-study">home-study</option>
              <option value="other">other</option>
            </select>
          </div>

          <div>
            <button class="btn btn-secondary" id="refreshBtn" type="button">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>

        </div>
      </div>

      <!-- Table -->
      <div class="card">
        <table>
          <thead>
            <tr>
              <th>Requirement</th>
              <th>File</th>
              <th>Status</th>
              <th>Uploaded</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="docsTbody">
            <tr><td colspan="5">Select a client to view documents.</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>
</div>

<!-- Modal -->
<div class="modal-overlay" id="docModal">
  <div class="modal">
    <div class="modal-header">
      <h3>Document Details</h3>
      <button class="btn btn-outline" id="closeModalBtn" type="button">&times;</button>
    </div>

    <div class="modal-body">
      <div class="grid">
        <div class="field"><small>Document ID</small><div id="m_doc_id">-</div></div>
        <div class="field"><small>Status</small><div id="m_status">-</div></div>

        <div class="field"><small>Client Email</small><div id="m_email">-</div></div>
        <div class="field"><small>User ID</small><div id="m_user_id">-</div></div>

        <div class="field"><small>Requirement</small><div id="m_req">-</div></div>
        <div class="field"><small>Category</small><div id="m_cat">-</div></div>

        <div class="field"><small>Original Name</small><div id="m_original">-</div></div>
        <div class="field"><small>File Size</small><div id="m_size">-</div></div>
      </div>

      <div style="margin-top:12px;">
        <div class="field">
          <small>File</small>
          <div id="m_file_link">-</div>
        </div>
      </div>

      <div style="margin-top:12px;">
        <label style="display:block;margin-bottom:6px;color:#555;font-size:13px;font-weight:600;">Review Notes</label>
        <textarea id="review_notes" placeholder="Write notes..."></textarea>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-danger" id="rejectBtn" type="button"><i class="fas fa-times"></i> Reject</button>
      <button class="btn btn-success" id="approveBtn" type="button"><i class="fas fa-check"></i> Approve</button>
    </div>
  </div>
</div>

<script>
const API_URL = "documents_review_api.php";

const tbody = document.getElementById('docsTbody');
const clientSelect = document.getElementById('clientSelect');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const categoryFilter = document.getElementById('categoryFilter');
const refreshBtn = document.getElementById('refreshBtn');

const docModal = document.getElementById('docModal');
const closeModalBtn = document.getElementById('closeModalBtn');

const m_doc_id = document.getElementById('m_doc_id');
const m_status = document.getElementById('m_status');
const m_email = document.getElementById('m_email');
const m_user_id = document.getElementById('m_user_id');
const m_req = document.getElementById('m_req');
const m_cat = document.getElementById('m_cat');
const m_original = document.getElementById('m_original');
const m_size = document.getElementById('m_size');
const m_file_link = document.getElementById('m_file_link');
const review_notes = document.getElementById('review_notes');

const approveBtn = document.getElementById('approveBtn');
const rejectBtn = document.getElementById('rejectBtn');

let allDocs = [];
let currentId = null;

function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}

function badge(st){
  st = String(st||'pending').toLowerCase();
  if(st === 'approved') return 'b-approved';
  if(st === 'rejected') return 'b-rejected';
  if(st === 'uploaded') return 'b-uploaded';
  return 'b-pending';
}

function openModal(){ docModal.style.display = 'flex'; }
function closeModal(){
  docModal.style.display = 'none';
  currentId = null;
  review_notes.value = '';
}

function requirementText(d){
  // If API sends requirement_name use it, else fallback to requirement_id
  if (d.requirement_name && d.requirement_name !== '-') return d.requirement_name;
  if (d.requirement_id) return `REQ-${d.requirement_id}`;
  return '-';
}

function render(list){
  tbody.innerHTML = list.map(d => `
    <tr>
      <td>
        <strong>${esc(requirementText(d))}</strong><br>
        <small>${esc(d.category || '-')}</small>
      </td>
      <td>
        <strong>${esc(d.original_name || d.file_name || '-')}</strong><br>
        <small>${esc(d.file_path || '')}</small>
      </td>
      <td><span class="badge ${badge(d.status)}">${esc(d.status || 'pending')}</span></td>
      <td>${esc(d.upload_date || '-')}</td>
      <td>
        <button class="btn btn-outline" data-action="view" data-id="${esc(d.id)}">View</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="5">No documents found</td></tr>`;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const st = statusFilter.value;
  const cat = categoryFilter.value;

  const list = allDocs.filter(d => {
    const hay = (
      requirementText(d) + ' ' +
      (d.original_name||'') + ' ' +
      (d.file_name||'') + ' ' +
      (d.category||'')
    ).toLowerCase();

    if (q && !hay.includes(q)) return false;
    if (st !== 'all' && String(d.status||'pending').toLowerCase() !== st) return false;
    if (cat !== 'all' && String(d.category||'').toLowerCase() !== cat) return false;
    return true;
  });

  render(list);
}

async function safeJson(res){
  const txt = await res.text();
  try { return JSON.parse(txt); } catch(e) { return {success:false, message:'Invalid JSON from API', raw:txt}; }
}

async function loadClients(){
  try {
    const res = await fetch(`${API_URL}?action=clients`);
    const data = await safeJson(res);

    if(!data.success){
      clientSelect.innerHTML = `<option value="">Failed to load clients</option>`;
      return;
    }

    const clients = data.clients || [];
    clientSelect.innerHTML =
      `<option value="">Select Client</option>` +
      clients.map(c => `<option value="${esc(c.id)}">${esc(c.email)} (ID: ${esc(c.id)})</option>`).join('');

  } catch(e){
    clientSelect.innerHTML = `<option value="">API error</option>`;
  }
}

async function loadDocsForClient(userId){
  if(!userId){
    allDocs = [];
    tbody.innerHTML = `<tr><td colspan="5">Select a client to view documents.</td></tr>`;
    return;
  }

  try {
    const res = await fetch(`${API_URL}?action=list&user_id=${encodeURIComponent(userId)}`);
    const data = await safeJson(res);

    if(!data.success){
      tbody.innerHTML = `<tr><td colspan="5">${esc(data.message || 'Failed')}</td></tr>`;
      return;
    }

    allDocs = data.documents || [];
    applyFilters();

  } catch(e){
    tbody.innerHTML = `<tr><td colspan="5">API error</td></tr>`;
  }
}

async function viewDoc(id){
  try {
    const res = await fetch(`${API_URL}?action=get&id=${encodeURIComponent(id)}`);
    const data = await safeJson(res);
    if(!data.success) return alert(data.message || 'Failed');

    const d = data.document;
    currentId = d.id;

    m_doc_id.textContent = `#${d.id}`;
    m_status.textContent = d.status || 'pending';
    m_email.textContent = d.client_email || '-';
    m_user_id.textContent = d.user_id || '-';
    m_req.textContent = requirementText(d);
    m_cat.textContent = d.category || '-';
    m_original.textContent = d.original_name || d.file_name || '-';
    m_size.textContent = d.file_size ? `${d.file_size} bytes` : '-';

    if(d.file_path){
      const safe = esc(d.file_path);
      m_file_link.innerHTML = `<a class="link" href="${safe}" target="_blank" rel="noopener">Open File</a>`;
    } else {
      m_file_link.textContent = '-';
    }

    review_notes.value = d.review_notes || '';
    openModal();

  } catch(e){
    alert('API error');
  }
}

async function postAction(action){
  if(!currentId) return;

  const form = new FormData();
  form.append('action', action);
  form.append('id', currentId);
  form.append('review_notes', review_notes.value.trim());

  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await safeJson(res);

  alert(data.message || (data.success ? 'Done' : 'Failed'));

  if(data.success){
    closeModal();
    loadDocsForClient(clientSelect.value);
  }
}

/* EVENTS */
tbody.addEventListener('click', (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;
  if(btn.dataset.action === 'view') viewDoc(btn.dataset.id);
});

approveBtn.addEventListener('click', ()=> postAction('approve'));
rejectBtn.addEventListener('click', ()=>{
  const notes = review_notes.value.trim();
  if(!notes) return alert('Please add reason in review notes before rejecting');
  postAction('reject');
});

closeModalBtn.addEventListener('click', closeModal);
docModal.addEventListener('click', (e)=>{ if(e.target === docModal) closeModal(); });

searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
categoryFilter.addEventListener('change', applyFilters);

clientSelect.addEventListener('change', ()=> loadDocsForClient(clientSelect.value));
refreshBtn.addEventListener('click', ()=> loadDocsForClient(clientSelect.value));


/* INIT */
loadClients();
</script>
</body>
</html>
