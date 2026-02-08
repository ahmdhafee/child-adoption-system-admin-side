<?php
require_once '../officer_auth.php';

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Management | Chief Officer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/children-management.css">
    <link rel="shortcut icon" href="../favlogo.png" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
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
                    <div style="font-size: 1.3rem;">Family Bridge</div>
                    <div class="admin-tag">Chief Officer Portal</div>
                </div>
            </div>
        </div>

        

        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
            <a href="users.php" class="nav-item">
                <i class="fas fa-users"></i><span>User Management</span>
            </a>
            <a href="children-management.php" class="nav-item">
                <i class="fas fa-child"></i><span>Children Management</span>
            </a>

            <!-- ✅ NEW: Institute Management -->
            <a href="institutes.php" class="nav-item active">
                <i class="fas fa-building"></i><span>Institute Management</span>
            </a>

            <a href="clients.php" class="nav-item">
                <i class="fas fa-user-friends"></i><span>Clients</span>
            </a>
            <a href="appointments.php" class="nav-item">
                <i class="fas fa-calendar-check"></i><span>Appointments</span>
            </a>
            <a href="inquiries.php" class="nav-item">
                <i class="fas fa-question-circle"></i><span>Inquiries</span>
            </a>
            <a href="guidelines.php" class="nav-item">
                <i class="fas fa-book"></i><span>Guidelines</span>
            </a>
            <a href="required_documents.php" class="nav-item ">
        <i class="fas fa-file-upload"></i><span>Required Documents</span>
      </a>
        </nav>

        <div class="logout-section">
            <button class="logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1>Institute Management</h1>
                    <p>Manage orphanages / child care institutions</p>
                </div>
            </div>

            <div class="header-right">
                <div class="admin-profile">
                    <div class="admin-avatar-sm">CO</div>
                    <div class="admin-info-sm">
                        <h4>Chief Officer</h4>
                        <p>Institute Management</p>
                    </div>
                </div>

                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search institutes...">
                    </div>
                    <button class="btn btn-primary" id="addInstituteBtn" type="button">
                        <i class="fas fa-plus"></i> Add Institute
                    </button>
                </div>
            </div>
        </header>

        <div class="content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-info">
                        <h3 id="totalInstitutes">0</h3>
                        <p>Total Institutes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3 id="activeInstitutes">0</h3>
                        <p>Active Institutes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-info">
                        <h3 id="inactiveInstitutes">0</h3>
                        <p>Inactive Institutes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-child"></i></div>
                    <div class="stat-info">
                        <h3 id="linkedChildren">-</h3>
                        <p>Children Linked</p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="children-table-container">
                <div class="table-header">
                    <h2>Institute Records</h2>
                    <div class="table-filters">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <table class="children-table" id="institutesTable">
                    <thead>
                        <tr>
                            <th>Institute</th>
                            <th>City</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="institutesBody"></tbody>
                </table>

                <div class="pagination" id="pagination">
                    <button class="page-btn" id="prevPage">&laquo;</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn" id="nextPage">&raquo;</button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Institute Modal -->
<div class="modal" id="instituteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Institute</h3>
            <button class="close-modal" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="instituteForm">
                <input type="hidden" id="instId">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Institute Name</label>
                        <input type="text" class="form-control" id="instName" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" id="instCity">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="instAddress" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="instContact">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="instPhone">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="instEmail">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="instStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelBtn" type="button">Cancel</button>
            <button class="btn btn-primary" id="saveBtn" type="button">Save Institute</button>
        </div>
    </div>
</div>

<script>
const API_URL = "institutes_api.php";

const institutesBody = document.getElementById('institutesBody');
const totalInstitutes = document.getElementById('totalInstitutes');
const activeInstitutes = document.getElementById('activeInstitutes');
const inactiveInstitutes = document.getElementById('inactiveInstitutes');

const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');

const addInstituteBtn = document.getElementById('addInstituteBtn');
const instituteModal = document.getElementById('instituteModal');
const closeModal = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const saveBtn = document.getElementById('saveBtn');

const instId = document.getElementById('instId');
const instName = document.getElementById('instName');
const instCity = document.getElementById('instCity');
const instAddress = document.getElementById('instAddress');
const instContact = document.getElementById('instContact');
const instPhone = document.getElementById('instPhone');
const instEmail = document.getElementById('instEmail');
const instStatus = document.getElementById('instStatus');
const modalTitle = document.getElementById('modalTitle');

const logoutBtn = document.getElementById('logoutBtn');

let allInstitutes = [];

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function openModal(){ instituteModal.style.display='block'; }
function closeModalFn(){
  instituteModal.style.display='none';
  instId.value='';
  document.getElementById('instituteForm').reset();
  modalTitle.textContent='Add Institute';
}

function render(list){
  institutesBody.innerHTML = list.map(i => `
    <tr>
      <td>
        <div style="display:flex;flex-direction:column;">
          <strong>${esc(i.name)}</strong>
          <small>${esc(i.institute_code || '')}</small>
        </div>
      </td>
      <td>${esc(i.city || '-')}</td>
      <td>
        <div style="display:flex;flex-direction:column;">
          <span>${esc(i.contact_person || '-')}</span>
          <small>${esc(i.phone || '')}</small>
        </div>
      </td>
      <td>${esc(i.status)}</td>
      <td>
        <button class="btn btn-secondary" data-action="edit" data-id="${esc(i.id)}">Edit</button>
        <button class="btn btn-danger" data-action="delete" data-id="${esc(i.id)}">Delete</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="5">No institutes found</td></tr>`;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const st = statusFilter.value;

  const filtered = allInstitutes.filter(i => {
    const hay = (i.name + ' ' + (i.institute_code || '')).toLowerCase();
    if(q && !hay.includes(q)) return false;
    if(st !== 'all' && i.status !== st) return false;
    return true;
  });

  render(filtered);
}

async function loadInstitutes(){
  const res = await fetch(`${API_URL}?action=list`);
  const data = await res.json();
  if(!data.success){
    institutesBody.innerHTML = `<tr><td colspan="5">${esc(data.message || 'Failed')}</td></tr>`;
    return;
  }
  allInstitutes = data.institutes || [];
  totalInstitutes.textContent = data.stats?.total ?? 0;
  activeInstitutes.textContent = data.stats?.active ?? 0;
  inactiveInstitutes.textContent = data.stats?.inactive ?? 0;
  applyFilters();
}

addInstituteBtn.addEventListener('click', ()=>{ openModal(); });
closeModal.addEventListener('click', closeModalFn);
cancelBtn.addEventListener('click', closeModalFn);

saveBtn.addEventListener('click', async ()=>{
  const name = instName.value.trim();
  if(!name){ alert('Institute name required'); return; }

  const form = new FormData();
  const isEdit = !!instId.value;
  form.append('action', isEdit ? 'update' : 'create');
  if(isEdit) form.append('id', instId.value);

  form.append('name', name);
  form.append('city', instCity.value.trim());
  form.append('address', instAddress.value.trim());
  form.append('contact_person', instContact.value.trim());
  form.append('phone', instPhone.value.trim());
  form.append('email', instEmail.value.trim());
  form.append('status', instStatus.value);

  const res = await fetch(API_URL, {method:'POST', body: form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Saved' : 'Failed'));
  if(data.success){ closeModalFn(); loadInstitutes(); }
});

institutesBody.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;

  const action = btn.dataset.action;
  const id = btn.dataset.id;

  if(action === 'delete'){
    if(!confirm('Delete this institute?')) return;
    const form = new FormData();
    form.append('action','delete');
    form.append('id', id);
    const res = await fetch(API_URL, {method:'POST', body:form});
    const data = await res.json();
    alert(data.message || (data.success ? 'Deleted' : 'Failed'));
    if(data.success) loadInstitutes();
  }

  if(action === 'edit'){
    const inst = allInstitutes.find(x => String(x.id) === String(id));
    if(!inst) return;

    instId.value = inst.id;
    instName.value = inst.name || '';
    instCity.value = inst.city || '';
    instAddress.value = inst.address || '';
    instContact.value = inst.contact_person || '';
    instPhone.value = inst.phone || '';
    instEmail.value = inst.email || '';
    instStatus.value = inst.status || 'active';

    modalTitle.textContent = 'Edit Institute';
    openModal();
  }
});

searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);

// logout
logoutBtn.addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

loadInstitutes();
</script>
</body>
</html>
