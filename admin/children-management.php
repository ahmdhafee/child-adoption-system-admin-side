<?php
require_once '../officer_auth.php';

$role = $_SESSION['officer_role'] ?? '';
if (!in_array($role, ['chief', 'admin'], true)) {
    die('Access denied');
}

$isChief = ($role === 'chief');
$isAdmin = ($role === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Children Management | Chief Officer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/children-management.css">
    <link rel="shortcut icon" href="../favlogo.png" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .badge-role{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.8rem;background:#eef2ff;color:#3730a3}
        .badge-role.admin{background:#fff7ed;color:#9a3412}
        .btn[disabled]{opacity:.6;cursor:not-allowed}
        .modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);align-items:center;justify-content:center;z-index:2000}
        .modal-content{background:#fff;border-radius:12px;max-width:900px;width:95%;max-height:90vh;overflow:auto}
        .modal-header,.modal-footer{padding:16px 18px;border-bottom:1px solid #eee}
        .modal-footer{border-top:1px solid #eee;border-bottom:none;display:flex;gap:10px;justify-content:flex-end}
        .modal-body{padding:18px}
        .close-modal{background:transparent;border:none;font-size:28px;cursor:pointer}
        .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
        .form-group{display:flex;flex-direction:column;gap:6px}
        .form-label{font-weight:600}
        .form-control{padding:10px 12px;border:1px solid #ddd;border-radius:10px;outline:none}
        .form-control:focus{border-color:#94a3b8}
        .full-width{grid-column:1/-1}
        @media (max-width: 768px){
            .form-grid{grid-template-columns:1fr}
        }
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
                    <div class="admin-tag"><?php echo $isChief ? 'Chief Officer Portal' : 'Admin Portal'; ?></div>
                </div>
            </div>
        </div>

        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="admin-name"><?php echo htmlspecialchars($_SESSION['officer_name'] ?? ($isChief ? 'Chief Officer' : 'Admin')); ?></div>
            <div class="admin-role">
                <?php echo $isChief ? 'System Administrator' : 'Officer'; ?>
                <span class="badge-role <?php echo $isAdmin ? 'admin' : ''; ?>" style="margin-left:8px;">
                    <?php echo strtoupper($role); ?>
                </span>
            </div>
        </div>

        <nav class="sidebar-nav">
        <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <a href="children-management.php" class="nav-item active"><i class="fas fa-child"></i><span>Children Management</span></a>
      <a href="inquiries.php" class="nav-item"><i class="fas fa-question-circle"></i><span>inquiries</span></a>
      <a href="clients.php" class="nav-item"><i class="fas fa-users"></i><span>Clients</span></a>
      <a href="appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>Appointments</span></a>
      <a href="documents_review.php" class="nav-item "><i class="fas fa-file-alt"></i> <span>Document Review</span></a>
     
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
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title">
                    <h1>Children Management</h1>
                    <p>
                        <?php if ($isChief): ?>
                            Manage children profiles, status, and adoption processes
                        <?php else: ?>
                            View children records (Sensitive info hidden for Admin)
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="header-right">
                <div class="admin-profile">
                    <div class="admin-avatar-sm"><?php echo $isChief ? 'CO' : 'AD'; ?></div>
                    <div class="admin-info-sm">
                        <h4><?php echo $isChief ? 'Chief Officer' : 'Admin'; ?></h4>
                        <p>Children Management</p>
                    </div>
                </div>

                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search children...">
                    </div>

                    <button class="notification-btn" type="button" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>

                    <?php if ($isChief): ?>
                        <button class="btn btn-primary" id="addChildBtn" type="button">
                            <i class="fas fa-plus"></i> Add Child
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content">

            <!-- Info banner -->
            <?php if ($isAdmin): ?>
                <div class="alert alert-warning" style="margin: 16px 0;">
                    <i class="fas fa-lock"></i>
                    <div>
                        <strong>Privacy Mode:</strong> As Admin, you cannot see the child photo, district, institute, or full name. You also cannot add/edit/delete children.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-baby"></i></div>
                    <div class="stat-info">
                        <h3 id="totalChildren">0</h3>
                        <p>Total Children</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-heart"></i></div>
                    <div class="stat-info">
                        <h3 id="availableChildren">0</h3>
                        <p>Available for Adoption</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="stat-info">
                        <h3 id="pendingChildren">0</h3>
                        <p>In Process</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-home"></i></div>
                    <div class="stat-info">
                        <h3 id="adoptedChildren">0</h3>
                        <p>Successfully Adopted</p>
                    </div>
                </div>
            </div>

            <!-- Children Table Container -->
            <div class="children-table-container">
                <div class="table-header">
                    <h2>Children Records</h2>
                    <div class="table-filters">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="adopted">Adopted</option>
                            <option value="pending">Pending</option>
                        </select>
                        <select class="filter-select" id="ageFilter">
                            <option value="all">All Ages</option>
                            <option value="0-2">0-2 years</option>
                            <option value="3-5">3-5 years</option>
                            <option value="6-10">6-10 years</option>
                            <option value="11+">11+ years</option>
                        </select>
                        <button class="btn btn-secondary" id="exportBtn" type="button">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>

                <table class="children-table" id="childrenTable">
                    <thead>
                    <tr>
                        <th>Child Info</th>
                        <th>Age</th>
                        <th>Date of Birth</th>
                        <th>Date Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="childrenTableBody">
                    <!-- inserted by JS -->
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination" id="pagination">
                    <button class="page-btn" id="prevPage">&laquo;</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn" id="nextPage">&raquo;</button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add/Edit Child Modal (Chief only but kept in DOM safely) -->
<div class="modal" id="childModal">
    <div class="modal-content">
        <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 id="modalTitle">Add New Child</h3>
            <button class="close-modal" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="childForm" enctype="multipart/form-data">
                <div class="form-grid">

                    <div class="form-group full-width" id="photoGroup">
                        <label class="form-label">Child Photo</label>
                        <input type="file" class="form-control" id="photo" accept="image/*">
                        <small style="color:#666;">Chief only. JPG/PNG/WEBP</small>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-control" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Blood Group</label>
                        <input type="text" class="form-control" id="bloodGroup" placeholder="O+, A-, etc">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Hair Color</label>
                        <input type="text" class="form-control" id="hairColor">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Eyes Color</label>
                        <input type="text" class="form-control" id="eyesColor">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Skin Color</label>
                        <input type="text" class="form-control" id="skinColor">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" class="form-control" id="heightCm" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" class="form-control" id="weightKg" step="0.01" min="0">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Religion</label>
                        <input type="text" class="form-control" id="religion">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="status" required>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="adopted">Adopted</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">District</label>
                        <input type="text" class="form-control" id="district">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Institute</label>
                        <select class="form-control" id="instituteId">
                            <option value="">Select Institute</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date Registered</label>
                        <input type="date" class="form-control" id="dateRegistered" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Medical Condition</label>
                        <textarea class="form-control" id="medicalCondition" rows="3"></textarea>
                    </div>

                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelBtn" type="button">Cancel</button>
            <button class="btn btn-primary" id="saveChildBtn" type="button">Save Child</button>
        </div>
    </div>
</div>

<!-- Child Details Modal -->
<div class="modal" id="childDetailsModal">
    <div class="modal-content">
        <div class="modal-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3>Child Details</h3>
            <button class="close-modal" id="closeDetailsModal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="childDetailsContent"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeDetailsBtn" type="button">Close</button>
            <button class="btn btn-primary" id="editChildBtn" type="button">Edit</button>
        </div>
    </div>
</div>

<script>
/* =========================
   CONFIG + PERMISSIONS
   ========================= */
const API_URL = "children_api.php";
const USER_ROLE = "<?php echo $role; ?>";
const IS_CHIEF = USER_ROLE === "chief";
const IS_ADMIN = USER_ROLE === "admin";

/* =========================
   ELEMENTS
   ========================= */
const childrenTableBody = document.getElementById('childrenTableBody');
const totalChildrenEl = document.getElementById('totalChildren');
const availableChildrenEl = document.getElementById('availableChildren');
const pendingChildrenEl = document.getElementById('pendingChildren');
const adoptedChildrenEl = document.getElementById('adoptedChildren');

const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const ageFilter = document.getElementById('ageFilter');

const addChildBtn = document.getElementById('addChildBtn');
const childModal = document.getElementById('childModal');
const closeModal = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const saveChildBtn = document.getElementById('saveChildBtn');

const childDetailsModal = document.getElementById('childDetailsModal');
const closeDetailsModal = document.getElementById('closeDetailsModal');
const closeDetailsBtn = document.getElementById('closeDetailsBtn');
const editChildBtn = document.getElementById('editChildBtn');

const instituteId = document.getElementById('instituteId');
const logoutBtn = document.getElementById('logoutBtn');

let allChildren = [];
let editingChildId = null;
let lastViewedId = null;

/* =========================
   HELPERS
   ========================= */
function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m =>
    ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])
  );
}

function matchAgeGroup(age, group){
  if(group === 'all') return true;
  if(group === '0-2') return age >= 0 && age <= 2;
  if(group === '3-5') return age >= 3 && age <= 5;
  if(group === '6-10') return age >= 6 && age <= 10;
  if(group === '11+') return age >= 11;
  return true;
}

/* =========================
   DROPDOWN: INSTITUTES
   ========================= */
async function loadInstitutesDropdown(){
  if(!instituteId) return;

  try{
    const res = await fetch('institutes_api.php?action=dropdown');
    const data = await res.json();
    if(!data.success) return;

    instituteId.innerHTML =
      `<option value="">Select Institute</option>` +
      (data.institutes || []).map(i =>
        `<option value="${esc(i.id)}">${esc(i.name)} (${esc(i.institute_code)})</option>`
      ).join('');

  }catch(e){
    console.error("Failed to load institutes", e);
  }
}

/* =========================
   RENDER TABLE
   ========================= */
function render(children){
  childrenTableBody.innerHTML = children.map(c => `
    <tr>
      <td>
        <div style="display:flex;flex-direction:column;">
          <strong>${esc(IS_ADMIN ? 'CONFIDENTIAL' : (c.full_name || c.name || ''))}</strong>
          <small>${esc(c.child_code || '')}</small>
        </div>
      </td>
      <td>${esc(c.age ?? '')}</td>
      <td>${esc(c.date_of_birth || '-')}</td>
      <td>${esc(c.added_at || '-')}</td>
      <td>${esc(c.status || '-')}</td>
      <td>
        <button class="btn btn-secondary" data-action="view" data-id="${esc(c.id)}">View</button>
        ${IS_CHIEF ? `
          <button class="btn btn-primary" data-action="edit" data-id="${esc(c.id)}">Edit</button>
          <button class="btn btn-danger" data-action="delete" data-id="${esc(c.id)}">Delete</button>
        ` : ``}
      </td>
    </tr>
  `).join('') || `<tr><td colspan="6">No children found</td></tr>`;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const st = statusFilter.value;
  const ag = ageFilter.value;

  const filtered = allChildren.filter(c => {
    const hay = ((c.full_name || c.name || '') + ' ' + (c.child_code || '')).toLowerCase();
    if(q && !hay.includes(q)) return false;
    if(st !== 'all' && c.status !== st) return false;
    if(!matchAgeGroup(parseInt(c.age || 0), ag)) return false;
    return true;
  });

  render(filtered);
}

function updateStats(stats){
  totalChildrenEl.textContent = stats.total_children ?? 0;
  availableChildrenEl.textContent = stats.available_children ?? 0;
  pendingChildrenEl.textContent = stats.pending_children ?? 0;
  adoptedChildrenEl.textContent = stats.adopted_children ?? 0;
}

/* =========================
   LOAD CHILDREN
   ========================= */
async function loadChildren(){
  try{
    const res = await fetch(`${API_URL}?action=list`);
    const data = await res.json();

    if(!data.success){
      childrenTableBody.innerHTML = `<tr><td colspan="6">${esc(data.message || 'Failed')}</td></tr>`;
      return;
    }

    allChildren = data.children || [];
    updateStats(data.stats || {});
    applyFilters();

  }catch(e){
    console.error(e);
    childrenTableBody.innerHTML = `<tr><td colspan="6">Server error</td></tr>`;
  }
}

/* =========================
   MODALS
   ========================= */
function openModal(){
  childModal.style.display = 'flex';
}
function closeModalFn(){
  childModal.style.display = 'none';
  editingChildId = null;
  document.getElementById('childForm').reset();
  if(instituteId) instituteId.value = '';
  document.getElementById('modalTitle').textContent = 'Add New Child';
}
function openDetailsModal(){
  childDetailsModal.style.display = 'flex';
}
function closeDetails(){
  childDetailsModal.style.display = 'none';
  lastViewedId = null;
}

/* Close modal on background click */
childModal.addEventListener('click', (e)=>{ if(e.target === childModal) closeModalFn(); });
childDetailsModal.addEventListener('click', (e)=>{ if(e.target === childDetailsModal) closeDetails(); });

/* Hide photo group if Admin */
if(IS_ADMIN){
  const photoGroup = document.getElementById('photoGroup');
  if(photoGroup) photoGroup.style.display = 'none';
  if(editChildBtn) editChildBtn.style.display = 'none';
}

/* Add button only for chief (button not present for admin anyway) */
if(addChildBtn){
  addChildBtn.addEventListener('click', ()=>{
    if(!IS_CHIEF) return;
    editingChildId = null;
    document.getElementById('modalTitle').textContent = 'Add New Child';
    openModal();
  });
}

if(closeModal) closeModal.addEventListener('click', closeModalFn);
if(cancelBtn) cancelBtn.addEventListener('click', closeModalFn);

if(closeDetailsModal) closeDetailsModal.addEventListener('click', closeDetails);
if(closeDetailsBtn) closeDetailsBtn.addEventListener('click', closeDetails);

/* =========================
   SAVE CHILD (CHIEF ONLY)
   ========================= */
async function saveChild(){
  if(!IS_CHIEF) return alert("Admin cannot add/edit children.");

  const fullName = document.getElementById('fullName').value.trim();
  const dob = document.getElementById('dob').value;
  const gender = document.getElementById('gender').value;
  const status = document.getElementById('status').value;
  const dateRegistered = document.getElementById('dateRegistered').value;

  if(!fullName || !dob || !gender || !status || !dateRegistered){
    alert('Please fill required fields.');
    return;
  }

  const form = new FormData();
  form.append('action', editingChildId ? 'update' : 'create');
  if(editingChildId) form.append('id', editingChildId);

  form.append('full_name', fullName);
  form.append('dob', dob);
  form.append('gender', gender);
  form.append('status', status);
  form.append('dateRegistered', dateRegistered);

  form.append('blood_group', document.getElementById('bloodGroup').value.trim());
  form.append('hair_color', document.getElementById('hairColor').value.trim());
  form.append('eyes_color', document.getElementById('eyesColor').value.trim());
  form.append('skin_color', document.getElementById('skinColor').value.trim());
  form.append('height_cm', document.getElementById('heightCm').value.trim());
  form.append('weight_kg', document.getElementById('weightKg').value.trim());
  form.append('religion', document.getElementById('religion').value.trim());
  form.append('medical_condition', document.getElementById('medicalCondition').value.trim());
  form.append('district', document.getElementById('district').value.trim());
  form.append('instituteId', instituteId ? instituteId.value : '');

  const photoInput = document.getElementById('photo');
  if(photoInput && photoInput.files && photoInput.files[0]){
    form.append('photo', photoInput.files[0]);
  }

  try{
    const res = await fetch(API_URL, { method:'POST', body: form });
    const data = await res.json();

    alert(data.message || (data.success ? 'Saved' : 'Failed'));
    if(data.success){
      closeModalFn();
      loadChildren();
    }
  }catch(e){
    console.error(e);
    alert('Server error');
  }
}

if(saveChildBtn) saveChildBtn.addEventListener('click', saveChild);

/* =========================
   VIEW DETAILS
   ========================= */
async function viewChild(id){
  try{
    const res = await fetch(`${API_URL}?action=get&id=${encodeURIComponent(id)}`);
    const data = await res.json();
    if(!data.success) return alert(data.message || 'Failed');

    const c = data.child;
    lastViewedId = id;

    let html = `<div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;">`;

    if(IS_CHIEF && c.photo){
      html += `
        <div style="width:180px;">
          <img src="../uploads/children/${esc(c.photo)}" alt="Child Photo"
               style="width:180px;height:180px;object-fit:cover;border-radius:12px;border:1px solid #eee;">
        </div>
      `;
    }

    html += `<div style="flex:1;min-width:240px;line-height:1.75;">`;
    html += `<p><strong>ID:</strong> ${esc(c.id)}</p>`;
    html += `<p><strong>Child Code:</strong> ${esc(c.child_code)}</p>`;
    html += `<p><strong>Full Name:</strong> ${esc(IS_ADMIN ? 'CONFIDENTIAL' : (c.full_name || ''))}</p>`;
    html += `<p><strong>Age:</strong> ${esc(c.age)}</p>`;
    html += `<p><strong>Gender:</strong> ${esc(c.gender)}</p>`;
    html += `<p><strong>Blood Group:</strong> ${esc(c.blood_group)}</p>`;
    html += `<p><strong>Hair Color:</strong> ${esc(c.hair_color)}</p>`;
    html += `<p><strong>Eyes Color:</strong> ${esc(c.eyes_color)}</p>`;
    html += `<p><strong>Skin Color:</strong> ${esc(c.skin_color)}</p>`;
    html += `<p><strong>Height:</strong> ${esc(c.height_cm)} cm</p>`;
    html += `<p><strong>Weight:</strong> ${esc(c.weight_kg)} kg</p>`;
    html += `<p><strong>Religion:</strong> ${esc(c.religion)}</p>`;
    html += `<p><strong>Medical Condition:</strong> ${esc(c.medical_condition)}</p>`;

    if(IS_CHIEF){
      html += `<p><strong>District:</strong> ${esc(c.district)}</p>`;
      html += `<p><strong>Institute:</strong> ${esc(c.institute_name || '')}</p>`;
    }

    html += `<p><strong>Status:</strong> ${esc(c.status)}</p>`;
    html += `<p><strong>Date of Birth:</strong> ${esc(c.date_of_birth || '')}</p>`;
    html += `<p><strong>Date Registered:</strong> ${esc(c.added_at || '')}</p>`;
    html += `</div></div>`;

    document.getElementById('childDetailsContent').innerHTML = html;

    if(editChildBtn){
      editChildBtn.style.display = IS_CHIEF ? 'inline-block' : 'none';
    }

    openDetailsModal();

  }catch(e){
    console.error(e);
    alert('Server error');
  }
}

/* =========================
   EDIT CHILD (CHIEF ONLY)
   ========================= */
async function editChild(id){
  if(!IS_CHIEF) return;

  try{
    const res = await fetch(`${API_URL}?action=get&id=${encodeURIComponent(id)}`);
    const data = await res.json();
    if(!data.success) return alert(data.message || 'Failed');

    const c = data.child;
    editingChildId = id;

    document.getElementById('modalTitle').textContent = 'Edit Child';

    document.getElementById('fullName').value = c.full_name || '';
    document.getElementById('dob').value = c.date_of_birth || '';
    document.getElementById('gender').value = c.gender || '';
    document.getElementById('status').value = c.status || 'available';
    document.getElementById('dateRegistered').value = (c.added_at || '').slice(0,10);

    document.getElementById('bloodGroup').value = c.blood_group || '';
    document.getElementById('hairColor').value = c.hair_color || '';
    document.getElementById('eyesColor').value = c.eyes_color || '';
    document.getElementById('skinColor').value = c.skin_color || '';
    document.getElementById('heightCm').value = c.height_cm || '';
    document.getElementById('weightKg').value = c.weight_kg || '';
    document.getElementById('religion').value = c.religion || '';
    document.getElementById('medicalCondition').value = c.medical_condition || '';
    document.getElementById('district').value = c.district || '';
    if(instituteId) instituteId.value = c.institute_id || '';

    openModal();
  }catch(e){
    console.error(e);
    alert('Server error');
  }
}

/* =========================
   DELETE CHILD (CHIEF ONLY)
   ========================= */
async function deleteChild(id){
  if(!IS_CHIEF) return alert("Admin cannot delete children.");

  if(!confirm('Delete this child record?')) return;

  const form = new FormData();
  form.append('action','delete');
  form.append('id', id);

  try{
    const res = await fetch(API_URL,{ method:'POST', body: form });
    const data = await res.json();
    alert(data.message || 'Done');
    if(data.success) loadChildren();
  }catch(e){
    console.error(e);
    alert('Server error');
  }
}

/* =========================
   TABLE ACTIONS
   ========================= */
childrenTableBody.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;

  const action = btn.dataset.action;
  const id = btn.dataset.id;

  if(action === 'view') return viewChild(id);
  if(action === 'edit') return editChild(id);
  if(action === 'delete') return deleteChild(id);
});

/* Details modal Edit button */
if(editChildBtn){
  editChildBtn.addEventListener('click', ()=>{
    if(!IS_CHIEF || !lastViewedId) return;
    closeDetails();
    editChild(lastViewedId);
  });
}

/* =========================
   FILTER EVENTS
   ========================= */
searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
ageFilter.addEventListener('change', applyFilters);

/* =========================
   LOGOUT
   ========================= */
logoutBtn.addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

/* =========================
   INIT
   ========================= */
loadChildren();
loadInstitutesDropdown();
</script>
</body>
</html>
