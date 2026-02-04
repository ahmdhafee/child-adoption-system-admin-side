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
    <title>User Management | Family Bridge Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/users.css">
    <link rel="shortcut icon" href="/favlogo.png">
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
                <a href="users.php" class="nav-item active">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="children-management.php" class="nav-item">
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
                <!-- Keep your button id/class; just do server logout -->
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
                        <h1>User Management</h1>
                        <p>Manage all users, their roles, and account status</p>
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

                    <div class="header-actions">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search users...">
                        </div>
                        <button class="notification-btn" type="button">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1>User Management Dashboard</h1>
                        <p>Manage all system users including couples, administrators, and chief officers</p>
                    </div>
                    <div class="header-actions-buttons">
                        <button class="btn btn-outline" id="exportUsersBtn">
                            <i class="fas fa-file-export"></i>
                            Export Users
                        </button>
                        <button class="btn btn-primary" id="addUserBtn">
                            <i class="fas fa-user-plus"></i>
                            Add New User
                        </button>
                    </div>
                </div>

                <!-- Stats Cards (values will be set by JS) -->
                <div class="stats-grid">
                    <div class="stat-card total-users">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-value" id="statTotalUsers">0</div>
                        <div class="stat-label">Total Users</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i> Live</div>
                    </div>

                    <div class="stat-card active-users">
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                        <div class="stat-value" id="statActiveUsers">0</div>
                        <div class="stat-label">Active Users</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i> Live</div>
                    </div>

                    <div class="stat-card pending-users">
                        <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                        <div class="stat-value" id="statPendingUsers">0</div>
                        <div class="stat-label">Pending Verification</div>
                        <div class="stat-change negative"><i class="fas fa-arrow-up"></i> Live</div>
                    </div>

                    <div class="stat-card inactive-users">
                        <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
                        <div class="stat-value" id="statInactiveUsers">0</div>
                        <div class="stat-label">Inactive Users</div>
                        <div class="stat-change negative"><i class="fas fa-arrow-up"></i> Live</div>
                    </div>

                    <div class="stat-card couples">
                        <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
                        <div class="stat-value" id="statCouples">0</div>
                        <div class="stat-label">Couples</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i> Live</div>
                    </div>

                    <div class="stat-card admins">
                        <div class="stat-icon"><i class="fas fa-user-cog"></i></div>
                        <div class="stat-value" id="statAdmins">0</div>
                        <div class="stat-label">Admin Staff</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i> Live</div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="searchInput">Search Users</label>
                            <div class="search-input-group">
                                <i class="fas fa-search"></i>
                                <input type="text" id="searchInput" class="filter-input" placeholder="Search by name, email, or ID...">
                            </div>
                        </div>
                        <div class="filter-group">
                            <label for="roleFilter">Filter by Role</label>
                            <select id="roleFilter" class="filter-select">
                                <option value="all">All Roles</option>
                                <option value="couple">Couple</option>
                                <option value="admin">Administrator</option>
                                <option value="chief">Chief Officer</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="statusFilter">Filter by Status</label>
                            <select id="statusFilter" class="filter-select">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="sortFilter">Sort By</label>
                            <select id="sortFilter" class="filter-select">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="name">Name A-Z</option>
                                <option value="role">Role</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="users-table-container">
                    <div class="table-header">
                        <h2>All Users</h2>
                        <div class="table-actions">
                            <button class="btn btn-sm btn-outline" id="refreshTableBtn">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn btn-sm btn-outline" id="bulkActionsBtn">
                                <i class="fas fa-tasks"></i>
                                Bulk Actions
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="users-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllCheckbox">
                                    </th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Registration Date</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Users will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination" id="pagination"></div>
                </div>

                <!-- (Modals remain unchanged) -->
                <!-- User Details Modal -->
                <div class="modal" id="userDetailsModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>User Details</h3>
                            <button class="modal-close" data-modal="userDetailsModal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div id="userDetailsContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline" data-modal="userDetailsModal">Close</button>
                            <button class="btn btn-secondary" id="editUserBtn">
                                <i class="fas fa-edit"></i>
                                Edit User
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div class="modal" id="deleteModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Confirm Deletion</h3>
                            <button class="modal-close" data-modal="deleteModal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div style="text-align: center; padding: 20px;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--error); margin-bottom: 20px;"></i>
                                <h4 style="color: var(--dark); margin-bottom: 10px;">Delete User Account</h4>
                                <p style="color: var(--gray); margin-bottom: 20px;" id="deleteMessage">
                                    Are you sure you want to delete this user account? This action cannot be undone.
                                </p>
                                <p style="color: var(--warning); font-size: 0.9rem; font-weight: 600;">
                                    <i class="fas fa-info-circle"></i>
                                    All associated data will be permanently removed from the system.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline" data-modal="deleteModal">Cancel</button>
                            <button class="btn btn-danger" id="confirmDeleteBtn">Delete User</button>
                        </div>
                    </div>
                </div>

                <!-- Add/Edit User Modal -->
                <div class="modal" id="userFormModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 id="modalTitle">Add New User</h3>
                            <button class="modal-close" data-modal="userFormModal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="userForm">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                    <div>
                                        <label for="firstName" style="display: block; margin-bottom: 8px; font-weight: 600;">First Name *</label>
                                        <input type="text" id="firstName" class="filter-input" placeholder="First name" required>
                                    </div>
                                    <div>
                                        <label for="lastName" style="display: block; margin-bottom: 8px; font-weight: 600;">Last Name *</label>
                                        <input type="text" id="lastName" class="filter-input" placeholder="Last name" required>
                                    </div>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600;">Email Address *</label>
                                    <input type="email" id="email" class="filter-input" placeholder="Email address" required>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                                    <div>
                                        <label for="userRole" style="display: block; margin-bottom: 8px; font-weight: 600;">User Role *</label>
                                        <select id="userRole" class="filter-select" required>
                                            <option value="couple">Couple</option>
                                            <option value="admin">Administrator</option>
                                            <option value="chief">Chief Officer</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="userStatus" style="display: block; margin-bottom: 8px; font-weight: 600;">Account Status *</label>
                                        <select id="userStatus" class="filter-select" required>
                                            <option value="active">Active</option>
                                            <option value="pending">Pending</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="margin-bottom: 20px;">
                                    <label for="notes" style="display: block; margin-bottom: 8px; font-weight: 600;">Notes</label>
                                    <textarea id="notes" class="filter-input" rows="3" placeholder="Add any additional notes..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-outline" data-modal="userFormModal">Cancel</button>
                            <button class="btn btn-primary" id="saveUserBtn">Save User</button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

<script>
/* ✅ Keep your IDs/classes. This JS only populates table and wires buttons. */

const API_URL = "users_api.php";

const tbody = document.getElementById('usersTableBody');
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');
const statusFilter = document.getElementById('statusFilter');
const sortFilter = document.getElementById('sortFilter');

const refreshBtn = document.getElementById('refreshTableBtn');
const addUserBtn = document.getElementById('addUserBtn');
const saveUserBtn = document.getElementById('saveUserBtn');
const logoutBtn = document.getElementById('logoutBtn');

// stats ids
const statTotalUsers = document.getElementById('statTotalUsers');
const statActiveUsers = document.getElementById('statActiveUsers');
const statPendingUsers = document.getElementById('statPendingUsers');
const statInactiveUsers = document.getElementById('statInactiveUsers');
const statCouples = document.getElementById('statCouples');
const statAdmins = document.getElementById('statAdmins');

let allUsers = [];

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function formatDate(dt){
  if(!dt) return '-';
  return dt;
}

function roleLabel(r){
  if(r==='couple') return 'Couple';
  if(r==='admin') return 'Administrator';
  if(r==='chief') return 'Chief Officer';
  return r;
}

function statusLabel(s){
  if(!s) return '-';
  return s;
}

function buildRow(u){
  return `
    <tr>
      <td><input type="checkbox" class="rowCheckbox" data-id="${esc(u.uid)}" data-source="${esc(u.source)}"></td>
      <td>
        <div style="display:flex;flex-direction:column;">
          <strong>${esc(u.name)}</strong>
          <small>${esc(u.email)}</small>
        </div>
      </td>
      <td>${esc(roleLabel(u.role))}</td>
      <td>${esc(statusLabel(u.status))}</td>
      <td>${esc(formatDate(u.created_at))}</td>
      <td>${esc(formatDate(u.last_login))}</td>
      <td>
        <button class="btn btn-sm btn-outline" data-action="view" data-uid="${esc(u.uid)}" data-source="${esc(u.source)}">View</button>
        <button class="btn btn-sm btn-outline" data-action="toggle" data-uid="${esc(u.uid)}" data-source="${esc(u.source)}">
          ${u.status === 'active' ? 'Suspend' : 'Activate'}
        </button>
        <button class="btn btn-sm btn-danger" data-action="delete" data-uid="${esc(u.uid)}" data-source="${esc(u.source)}">Delete</button>
      </td>
    </tr>
  `;
}

function render(users){
  tbody.innerHTML = users.map(buildRow).join('') || `<tr><td colspan="7">No users found</td></tr>`;
}

function computeStats(users){
  const total = users.length;
  const active = users.filter(u => u.status === 'active').length;
  const pending = users.filter(u => u.status === 'pending').length;
  const inactive = users.filter(u => u.status === 'inactive' || u.status === 'suspended').length;
  const couples = users.filter(u => u.role === 'couple').length;
  const admins = users.filter(u => u.role === 'admin').length;

  statTotalUsers.textContent = total;
  statActiveUsers.textContent = active;
  statPendingUsers.textContent = pending;
  statInactiveUsers.textContent = inactive;
  statCouples.textContent = couples;
  statAdmins.textContent = admins;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const role = roleFilter.value;
  const status = statusFilter.value;
  const sort = sortFilter.value;

  let filtered = allUsers.filter(u => {
    const hay = (u.name + ' ' + u.email + ' ' + u.uid).toLowerCase();
    if(q && !hay.includes(q)) return false;
    if(role !== 'all' && u.role !== role) return false;
    if(status !== 'all') {
      // normalize: we treat officers.suspended as suspended, users.suspended as suspended
      if(u.status !== status) return false;
    }
    return true;
  });

  if(sort === 'newest') filtered.sort((a,b)=> (b.created_at||'').localeCompare(a.created_at||''));
  if(sort === 'oldest') filtered.sort((a,b)=> (a.created_at||'').localeCompare(b.created_at||''));
  if(sort === 'name') filtered.sort((a,b)=> (a.name||'').localeCompare(b.name||''));
  if(sort === 'role') filtered.sort((a,b)=> (a.role||'').localeCompare(b.role||''));

  render(filtered);
  computeStats(allUsers); // stats from all users
}

async function loadUsers(){
  const res = await fetch(`${API_URL}?action=list`);
  const data = await res.json();
  if(!data.success){
    tbody.innerHTML = `<tr><td colspan="7">${esc(data.message || 'Failed to load users')}</td></tr>`;
    return;
  }
  allUsers = data.users || [];
  applyFilters();
}

async function toggleStatus(uid, source){
  const form = new FormData();
  form.append('action','toggle');
  form.append('uid', uid);
  form.append('source', source);

  const res = await fetch(API_URL, {method:'POST', body:form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Updated' : 'Failed'));
  if(data.success) loadUsers();
}

async function deleteUser(uid, source){
  if(!confirm('Are you sure you want to delete this user?')) return;

  const form = new FormData();
  form.append('action','delete');
  form.append('uid', uid);
  form.append('source', source);

  const res = await fetch(API_URL, {method:'POST', body:form});
  const data = await res.json();
  alert(data.message || (data.success ? 'Deleted' : 'Failed'));
  if(data.success) loadUsers();
}

async function createUser(){
  // Takes values from your modal inputs
  const firstName = document.getElementById('firstName').value.trim();
  const lastName  = document.getElementById('lastName').value.trim();
  const email     = document.getElementById('email').value.trim();
  const role      = document.getElementById('userRole').value;
  const status    = document.getElementById('userStatus').value;

  if(!firstName || !lastName || !email){
    alert('Please fill required fields.');
    return;
  }

  const form = new FormData();
  form.append('action', 'create');
  form.append('firstName', firstName);
  form.append('lastName', lastName);
  form.append('email', email);
  form.append('role', role);
  form.append('status', status);

  const res = await fetch(API_URL, {method:'POST', body:form});
  const data = await res.json();
  if(!data.success){
    alert(data.message || 'Failed to create user');
    return;
  }

  // Show temp password for officers (admin)
  if(data.tempPassword){
    alert(`User created!\nTemporary password: ${data.tempPassword}\n(Ask them to change after login)`);
  } else {
    alert('User created successfully.');
  }

  loadUsers();
}

// Event wiring
searchInput.addEventListener('input', applyFilters);
roleFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);
sortFilter.addEventListener('change', applyFilters);

refreshBtn.addEventListener('click', loadUsers);

saveUserBtn.addEventListener('click', createUser);

tbody.addEventListener('click', (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;

  const action = btn.dataset.action;
  const uid = btn.dataset.uid;
  const source = btn.dataset.source;

  if(action === 'toggle') toggleStatus(uid, source);
  if(action === 'delete') deleteUser(uid, source);
  if(action === 'view') alert(`View user: ${uid} (${source})\n(Next we can connect this to your User Details modal)`);
});

// Logout -> real server logout (keep same button id)
logoutBtn.addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

// First load
loadUsers();
</script>

</body>
</html>
