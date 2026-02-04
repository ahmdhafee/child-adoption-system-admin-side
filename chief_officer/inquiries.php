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
    <title>Inquiries Management | Family Bridge Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/Inquires.css">
    <link rel="shortcut icon" href="../favlogo.png" type="logo">
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
                <a href="Inquires.php" class="nav-item active">
                    <i class="fas fa-question-circle"></i>
                    <span>Inquiries</span>
                    <span class="badge" id="inquiryBadge">0</span>
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
                        <h1>Inquiries Management</h1>
                        <p>View, manage, and respond to client inquiries and questions.</p>
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
                            <input type="text" id="searchInput" placeholder="Search inquiries...">
                        </div>
                        <button class="notification-btn" type="button">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationBadge">0</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1>Client Inquiries</h1>
                        <p>Manage all incoming questions and requests from prospective adoptive parents.</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-outline" id="exportBtn" type="button">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button class="btn btn-primary" id="markAllReadBtn" type="button">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card new">
                        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="newCount">0</div>
                            <div class="stat-label">New Inquiries</div>
                        </div>
                    </div>

                    <div class="stat-card in-progress">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="inProgressCount">0</div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>

                    <div class="stat-card resolved">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="resolvedCount">0</div>
                            <div class="stat-label">Resolved</div>
                        </div>
                    </div>

                    <div class="stat-card urgent">
                        <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div class="stat-content">
                            <div class="stat-value" id="urgentCount">0</div>
                            <div class="stat-label">Urgent</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter" class="filter-select">
                            <option value="all">All Statuses</option>
                            <option value="new">New</option>
                            <option value="inprogress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="priorityFilter">Priority</label>
                        <select id="priorityFilter" class="filter-select">
                            <option value="all">All Priorities</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="dateFilter">Date Range</label>
                        <input type="text" id="dateFilter" class="filter-input" placeholder="(not implemented)">
                    </div>

                    <div class="filter-actions">
                        <button class="btn btn-secondary" id="applyFiltersBtn" type="button">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <button class="btn btn-outline" id="clearFiltersBtn" type="button">
                            <i class="fas fa-redo"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Inquiries Table -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>All Inquiries</h2>
                        <div class="card-header-actions">
                            <button class="btn btn-sm btn-outline" id="refreshBtn" type="button">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="inquiries-table">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Inquirer</th>
                                    <th style="width: 25%;">Subject & Message</th>
                                    <th style="width: 10%;">Type</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Priority</th>
                                    <th style="width: 15%;">Date</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inquiriesTableBody"></tbody>
                        </table>

                        <div id="emptyState" class="empty-state" style="display:none;">
                            <i class="fas fa-envelope-open"></i>
                            <h3>No Inquiries Found</h3>
                            <p>There are no inquiries matching your current filters.</p>
                            <button class="btn btn-secondary" id="resetFiltersBtn" type="button">
                                <i class="fas fa-redo"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination (UI only) -->
                <div class="pagination">
                    <button class="pagination-btn" id="prevPageBtn" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="pagination-pages" id="paginationPages"></div>
                    <button class="pagination-btn" id="nextPageBtn">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="inquiryModal" style="display:none;">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Inquiry Details</h3>
                <button class="modal-close" id="modalClose"><i class="fas fa-times"></i></button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Inquirer</label>
                    <div class="inquiry-info" style="margin-bottom: 15px;">
                        <div class="inquirer-avatar"><i class="fas fa-user"></i></div>
                        <div class="inquirer-details">
                            <div class="inquirer-name" id="modalInquirerName"></div>
                            <div class="inquirer-email" id="modalInquirerEmail"></div>
                            <div class="inquirer-phone" id="modalInquirerPhone">(optional)</div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Inquiry Type</label>
                        <div id="modalInquiryType" class="inquiry-type" style="margin-top: 5px;"></div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div id="modalStatus" class="status-badge status-new" style="margin-top: 5px;">New</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <div id="modalSubject" style="padding: 10px; background-color: var(--light-gray); border-radius: var(--border-radius);"></div>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <div id="modalMessage" style="padding: 15px; background-color: var(--light-gray); border-radius: var(--border-radius); line-height: 1.6;"></div>
                </div>

                <div class="form-group">
                    <label>Response</label>
                    <textarea class="form-control" id="responseTextarea" placeholder="Type your response here..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Update Status</label>
                        <select class="form-control" id="updateStatusSelect">
                            <option value="new">New</option>
                            <option value="inprogress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select class="form-control" id="updatePrioritySelect">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-outline" id="closeModalBtn" type="button">Close</button>
                <button class="btn btn-success" id="sendResponseBtn" type="button">
                    <i class="fas fa-paper-plane"></i> Send Response
                </button>
            </div>
        </div>
    </div>

<script>
const API_LIST = "api/get_inquiries.php";
const API_ONE  = "api/get_inquiry.php";
const API_REPLY = "api/reply_inquiry.php";
const API_MARK_ALL = "api/mark_all_read.php";

const tbody = document.getElementById('inquiriesTableBody');
const emptyState = document.getElementById('emptyState');

const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const priorityFilter = document.getElementById('priorityFilter');

const refreshBtn = document.getElementById('refreshBtn');
const applyFiltersBtn = document.getElementById('applyFiltersBtn');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const resetFiltersBtn = document.getElementById('resetFiltersBtn');
const markAllReadBtn = document.getElementById('markAllReadBtn');

const inquiryBadge = document.getElementById('inquiryBadge');
const notificationBadge = document.getElementById('notificationBadge');

const newCount = document.getElementById('newCount');
const inProgressCount = document.getElementById('inProgressCount');
const resolvedCount = document.getElementById('resolvedCount');
const urgentCount = document.getElementById('urgentCount');

const inquiryModal = document.getElementById('inquiryModal');
const modalClose = document.getElementById('modalClose');
const closeModalBtn = document.getElementById('closeModalBtn');
const sendResponseBtn = document.getElementById('sendResponseBtn');

let allInquiries = [];
let currentInquiryId = null;

function esc(str){
  return String(str ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}
function cap(t){ return t ? t.charAt(0).toUpperCase() + t.slice(1) : ''; }

async function loadInquiries(){
  const res = await fetch(API_LIST);
  const data = await res.json();
  allInquiries = Array.isArray(data) ? data : [];
  applyFilters();
  updateStats(allInquiries);
}

function updateStats(list){
  const n = list.filter(x => x.status === 'new').length;
  const p = list.filter(x => x.status === 'inprogress').length;
  const r = list.filter(x => x.status === 'resolved').length;
  const urgent = list.filter(x => x.priority === 'high').length;

  inquiryBadge.textContent = n;
  notificationBadge.textContent = n;

  newCount.textContent = n;
  inProgressCount.textContent = p;
  resolvedCount.textContent = r;
  urgentCount.textContent = urgent;
}

function applyFilters(){
  const q = searchInput.value.trim().toLowerCase();
  const st = statusFilter.value;
  const pr = priorityFilter.value;

  let filtered = allInquiries.filter(inq => {
    const hay = (inq.client_name + ' ' + inq.client_email + ' ' + inq.subject + ' ' + inq.message).toLowerCase();
    if (q && !hay.includes(q)) return false;
    if (st !== 'all' && inq.status !== st) return false;
    if (pr !== 'all' && inq.priority !== pr) return false;
    return true;
  });

  renderTable(filtered);
}

function renderTable(list){
  tbody.innerHTML = '';
  if (!list.length){
    emptyState.style.display = 'block';
    return;
  }
  emptyState.style.display = 'none';

  list.forEach(inq => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <strong>${esc(inq.client_name)}</strong><br>
        <small>${esc(inq.client_email)}</small>
      </td>
      <td>
        <strong>${esc(inq.subject)}</strong>
        <p style="font-size:0.85rem;color:#555">${esc(inq.message).substring(0,80)}...</p>
      </td>
      <td>${cap(inq.type)}</td>
      <td><span class="status-badge status-${inq.status}">${cap(inq.status)}</span></td>
      <td>${cap(inq.priority)}</td>
      <td>${new Date(inq.created_at).toLocaleDateString()}</td>
      <td>
        <button class="btn btn-sm btn-outline" onclick="viewInquiry(${inq.id})">View</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// View Inquiry -> open modal
async function viewInquiry(id){
  const res = await fetch(`${API_ONE}?id=${id}`);
  const data = await res.json();
  if (!data.success){
    alert(data.message || 'Failed to load inquiry');
    return;
  }
  const inq = data.inquiry;
  currentInquiryId = inq.id;

  document.getElementById('modalInquirerName').textContent = inq.client_name;
  document.getElementById('modalInquirerEmail').textContent = inq.client_email;
  document.getElementById('modalInquiryType').textContent = cap(inq.type);

  const statusDiv = document.getElementById('modalStatus');
  statusDiv.textContent = cap(inq.status);
  statusDiv.className = `status-badge status-${inq.status}`;

  document.getElementById('modalSubject').textContent = inq.subject;
  document.getElementById('modalMessage').textContent = inq.message;

  document.getElementById('responseTextarea').value = inq.reply_message || '';
  document.getElementById('updateStatusSelect').value = inq.status;
  document.getElementById('updatePrioritySelect').value = inq.priority;

  inquiryModal.style.display = 'block';
}
window.viewInquiry = viewInquiry;

// close modal
modalClose.addEventListener('click', ()=> inquiryModal.style.display = 'none');
closeModalBtn.addEventListener('click', ()=> inquiryModal.style.display = 'none');

// send response + email
sendResponseBtn.addEventListener('click', async ()=>{
  if (!currentInquiryId) return alert('No inquiry selected');
  const response = document.getElementById('responseTextarea').value.trim();
  const st = document.getElementById('updateStatusSelect').value;
  const pr = document.getElementById('updatePrioritySelect').value;

  if (!response) return alert('Please type a response');

  const form = new FormData();
  form.append('id', currentInquiryId);
  form.append('response', response);
  form.append('status', st);
  form.append('priority', pr);

  const res = await fetch(API_REPLY, {method:'POST', body: form});
  const data = await res.json();
  alert(data.message || 'Done');

  if (data.success){
    inquiryModal.style.display = 'none';
    loadInquiries();
  }
});

refreshBtn.addEventListener('click', loadInquiries);
applyFiltersBtn.addEventListener('click', applyFilters);
clearFiltersBtn.addEventListener('click', ()=>{
  searchInput.value = '';
  statusFilter.value = 'all';
  priorityFilter.value = 'all';
  applyFilters();
});
resetFiltersBtn.addEventListener('click', ()=>{
  searchInput.value = '';
  statusFilter.value = 'all';
  priorityFilter.value = 'all';
  applyFilters();
});
searchInput.addEventListener('input', applyFilters);
statusFilter.addEventListener('change', applyFilters);
priorityFilter.addEventListener('change', applyFilters);

// mark all read
markAllReadBtn.addEventListener('click', async ()=>{
  const res = await fetch(API_MARK_ALL);
  const data = await res.json();
  alert(data.message || 'Done');
  loadInquiries();
});

// logout
document.getElementById('logoutBtn').addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

loadInquiries();
</script>

</body>
</html>
