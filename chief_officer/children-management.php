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
    <title>Children Management | Chief Officer Dashboard</title>
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
                <a href="children-management.php" class="nav-item active">
                    <i class="fas fa-child"></i>
                    <span>Children Management</span>
                </a>
                <a href="clients.php" class="nav-item">
                    <i class="fas fa-user-friends"></i>
                    <span>Clients</span>
                </a>
                <a href="appointments.php" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <a href="inquiries.php" class="nav-item">
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
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-title">
                        <h1>Children Management</h1>
                        <p>Manage children profiles, status, and adoption processes</p>
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar-sm">CO</div>
                        <div class="admin-info-sm">
                            <h4>Chief Officer</h4>
                            <p>Children Management</p>
                        </div>
                    </div>
                    
                    <div class="header-actions">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Search children...">
                        </div>
                        <button class="notification-btn" type="button">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        <button class="btn btn-primary" id="addChildBtn" type="button">
                            <i class="fas fa-plus"></i> Add Child
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
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
                            <!-- Children data will be inserted here by JavaScript -->
                        </tbody>
                    </table>

                    <!-- Pagination (we will do simple page 1 only now; can expand later) -->
                    <div class="pagination" id="pagination">
                        <button class="page-btn" id="prevPage">&laquo;</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn" id="nextPage">&raquo;</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Child Modal -->
    <div class="modal" id="childModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Child</h3>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="childForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" required>
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
                            <label class="form-label">Status</label>
                            <select class="form-control" id="status" required>
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="adopted">Adopted</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Registered</label>
                            <input type="date" class="form-control" id="dateRegistered" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Medical History</label>
                            <textarea class="form-control" id="medicalHistory" rows="3" placeholder="Enter medical history..."></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Special Notes</label>
                            <textarea class="form-control" id="specialNotes" rows="2" placeholder="Any special notes..."></textarea>
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
    <div class="modal child-details-modal" id="childDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
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
/* ✅ IMPORTANT: do not change IDs/classes. This JS only uses your IDs. */

const API_URL = "children_api.php";

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

const logoutBtn = document.getElementById('logoutBtn');

let allChildren = [];
let editingChildId = null;

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function calcAgeFromDob(dob){
  if(!dob) return '';
  const d = new Date(dob);
  if(isNaN(d)) return '';
  const diff = Date.now() - d.getTime();
  const age = new Date(diff).getUTCFullYear() - 1970;
  return age;
}

function matchAgeGroup(age, group){
  if(group === 'all') return true;
  if(group === '0-2') return age >= 0 && age <= 2;
  if(group === '3-5') return age >= 3 && age <= 5;
  if(group === '6-10') return age >= 6 && age <= 10;
  if(group === '11+') return age >= 11;
  return true;
}

function render(children){
  childrenTableBody.innerHTML = children.map(c => `
    <tr>
      <td>
        <div style="display:flex;flex-direction:column;">
          <strong>${esc(c.name)}</strong>
          <small>${esc(c.child_code)}</small>
        </div>
      </td>
      <td>${esc(c.age)}</td>
      <td>${esc(c.date_of_birth || '-')}</td>
      <td>${esc(c.added_at || '-')}</td>
      <td>${esc(c.status)}</td>
      <td>
        <button class="btn btn-secondary" data-action="view" data-id="${esc(c.id)}">View</button>
        <button class="btn btn-primary" data-action="edit" data-id="${esc(c.id)}">Edit</button>
        <button class="btn btn-danger" data-action="delete" data-id="${esc(c.id)}">Delete</button>
      </td>
    </tr>
  `).join('') || `<tr><td colspan="6">No children found</td></tr>`;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const st = statusFilter.value;
  const ag = ageFilter.value;

  let filtered = allChildren.filter(c => {
    const hay = (c.name + ' ' + c.child_code).toLowerCase();
    if(q && !hay.includes(q)) return false;
    if(st !== 'all' && c.status !== st) return false;
    if(!matchAgeGroup(parseInt(c.age || 0, 10), ag)) return false;
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

async function loadChildren(){
  const res = await fetch(`${API_URL}?action=list`);
  const data = await res.json();
  if(!data.success){
    childrenTableBody.innerHTML = `<tr><td colspan="6">${esc(data.message || 'Failed')}</td></tr>`;
    return;
  }
  allChildren = data.children || [];
  updateStats(data.stats || {});
  applyFilters();
}

function openModal(){
  childModal.style.display = 'block';
}
function closeModalFn(){
  childModal.style.display = 'none';
  editingChildId = null;
  document.getElementById('childForm').reset();
  document.getElementById('modalTitle').textContent = 'Add New Child';
}
function openDetailsModal(){
  childDetailsModal.style.display = 'block';
}
function closeDetails(){
  childDetailsModal.style.display = 'none';
}

addChildBtn.addEventListener('click', ()=>{
  editingChildId = null;
  document.getElementById('modalTitle').textContent = 'Add New Child';
  openModal();
});
closeModal.addEventListener('click', closeModalFn);
cancelBtn.addEventListener('click', closeModalFn);

closeDetailsModal.addEventListener('click', closeDetails);
closeDetailsBtn.addEventListener('click', closeDetails);

async function saveChild(){
  const firstName = document.getElementById('firstName').value.trim();
  const lastName = document.getElementById('lastName').value.trim();
  const dob = document.getElementById('dob').value;
  const gender = document.getElementById('gender').value;
  const status = document.getElementById('status').value;
  const dateRegistered = document.getElementById('dateRegistered').value;
  const medicalHistory = document.getElementById('medicalHistory').value.trim();
  const specialNotes = document.getElementById('specialNotes').value.trim();

  if(!firstName || !lastName || !dob || !gender || !status || !dateRegistered){
    alert('Please fill required fields.');
    return;
  }

  const form = new FormData();
  form.append('action', editingChildId ? 'update' : 'create');
  if(editingChildId) form.append('id', editingChildId);
  form.append('firstName', firstName);
  form.append('lastName', lastName);
  form.append('dob', dob);
  form.append('gender', gender);
  form.append('status', status);
  form.append('dateRegistered', dateRegistered);
  form.append('medicalHistory', medicalHistory);
  form.append('specialNotes', specialNotes);

  const res = await fetch(API_URL, {method:'POST', body:form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Saved' : 'Failed'));

  if(data.success){
    closeModalFn();
    loadChildren();
  }
}

saveChildBtn.addEventListener('click', saveChild);

childrenTableBody.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;

  const action = btn.dataset.action;
  const id = btn.dataset.id;

  if(action === 'delete'){
    if(!confirm('Delete this child record?')) return;
    const form = new FormData();
    form.append('action','delete');
    form.append('id', id);
    const res = await fetch(API_URL, {method:'POST', body:form});
    const data = await res.json();
    alert(data.message || (data.success ? 'Deleted' : 'Failed'));
    if(data.success) loadChildren();
  }

  if(action === 'edit'){
    const child = allChildren.find(x => String(x.id) === String(id));
    if(!child) return;
    editingChildId = id;
    document.getElementById('modalTitle').textContent = 'Edit Child';
    const parts = (child.name || '').split(' ');
    document.getElementById('firstName').value = parts[0] || '';
    document.getElementById('lastName').value = parts.slice(1).join(' ') || '';
    document.getElementById('dob').value = child.date_of_birth || '';
    document.getElementById('gender').value = child.gender || '';
    document.getElementById('status').value = child.status || 'available';
    document.getElementById('dateRegistered').value = (child.added_at || '').slice(0,10) || '';
    document.getElementById('medicalHistory').value = child.health_status || '';
    document.getElementById('specialNotes').value = child.special_needs || '';
    openModal();
  }

  if(action === 'view'){
    const child = allChildren.find(x => String(x.id) === String(id));
    if(!child) return;
    document.getElementById('childDetailsContent').innerHTML = `
      <div style="line-height:1.7">
        <p><b>Code:</b> ${esc(child.child_code)}</p>
        <p><b>Name:</b> ${esc(child.name)}</p>
        <p><b>Age:</b> ${esc(child.age)}</p>
        <p><b>DOB:</b> ${esc(child.date_of_birth || '-')}</p>
        <p><b>Gender:</b> ${esc(child.gender)}</p>
        <p><b>Status:</b> ${esc(child.status)}</p>
        <p><b>Health Status:</b> ${esc(child.health_status || '-')}</p>
        <p><b>Special Needs:</b> ${esc(child.special_needs || '-')}</p>
        <p><b>Location:</b> ${esc(child.location || '-')}</p>
        <p><b>Background:</b> ${esc(child.background || '-')}</p>
      </div>
    `;
    openDetailsModal();
    // quick edit button
    editChildBtn.onclick = ()=> {
      closeDetails();
      btn.closest('tr').querySelector('[data-action="edit"]').click();
    };
  }
});

searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
ageFilter.addEventListener('change', applyFilters);

// logout
logoutBtn.addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

// initial load
loadChildren();
</script>

</body>
</html>
