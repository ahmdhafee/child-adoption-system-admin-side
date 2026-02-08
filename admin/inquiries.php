<?php
require_once '../officer_auth.php';

if ($_SESSION['officer_role'] !== 'admin') {
    die('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | Family Bridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/Inquires.css">
    <link rel="shortcut icon" href="favlogo.png" type="logo">
    <style>
        /* Base Styles - Same as dashboard */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
                    <div>Admin Panel</div>
                </div>
            </div>
        </div>
            
            <nav class="sidebar-nav">
            <a href="index.php"  class="nav-item">
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

            <a href="clients.php" class="nav-item">
                <i class="fas fa-users"></i>
                Clients
            </a>

            <a href="appointments.php" class="nav-item">
                <i class="fas fa-calendar-check"></i>
                Appointments
               
            </a>
            <a href="documents_review.php" class="nav-item ">
                <i class="fas fa-file-alt"></i>
                 <span>Document Review</span>
                </a>
            
            </nav>
            
            
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
                        <p>Manage client questions, support requests, and feedback</p>
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

               
                <a class="logout-btn" href="../officer_logout.php" style="text-decoration:none;">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <!-- Statistics -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon icon-total">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="stat-number" id="totalInquiries">48</div>
                        <div class="stat-label">Total Inquiries</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-new">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-number" id="newInquiries">12</div>
                        <div class="stat-label">New</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number" id="pendingInquiries">24</div>
                        <div class="stat-label">Pending Response</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-resolved">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number" id="resolvedInquiries">12</div>
                        <div class="stat-label">Resolved</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <button class="btn btn-primary" id="markAllReadBtn">
                        <i class="fas fa-envelope-open"></i> Mark All as Read
                    </button>
                    <button class="btn btn-secondary" id="exportInquiriesBtn">
                        <i class="fas fa-download"></i> Export Inquiries
                    </button>
                    <button class="btn btn-accent" id="bulkReplyBtn">
                        <i class="fas fa-reply-all"></i> Bulk Reply
                    </button>
                </div>

                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active" data-status="all">
                        All Inquiries
                        <span class="badge badge-normal" id="allCount">48</span>
                    </button>
                    <button class="tab" data-status="new">
                        New
                        <span class="badge badge-new" id="newCount">12</span>
                    </button>
                    <button class="tab" data-status="pending">
                        Pending
                        <span class="badge badge-pending" id="pendingCount">24</span>
                    </button>
                    <button class="tab" data-status="resolved">
                        Resolved
                        <span class="badge badge-resolved" id="resolvedCount">12</span>
                    </button>
                    <button class="tab" data-status="high">
                        High Priority
                        <span class="badge badge-high" id="highCount">5</span>
                    </button>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <label class="form-label">Search Inquiries</label>
                        <input type="text" class="form-control" id="searchInquiries" placeholder="Search by name, email, or subject...">
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Category</label>
                        <select class="form-control" id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="general">General Inquiry</option>
                            <option value="adoption">Adoption Process</option>
                            <option value="eligibility">Eligibility</option>
                            <option value="technical">Technical Support</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Date Range</label>
                        <input type="date" class="form-control" id="dateFrom">
                    </div>
                    <div class="filter-group">
                        <label class="form-label">To</label>
                        <input type="date" class="form-control" id="dateTo">
                    </div>
                    <div class="filter-group">
                        <button class="btn btn-secondary" id="clearFilters">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Inquiries List -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Client Inquiries</h2>
                        <div class="card-actions">
                            <button class="btn btn-primary" id="refreshInquiries">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="inquiries-list" id="inquiriesList">
                            <!-- Inquiries will be loaded here -->
                        </div>
                        
                        <!-- No Results Message -->
                        <div id="noResults" class="no-results" style="display: none;">
                            <i class="fas fa-search"></i>
                            <h3>No Inquiries Found</h3>
                            <p>Try adjusting your search or filter criteria</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div style="display: flex; justify-content: center; margin-top: 30px;" id="pagination">
                    <!-- Pagination will be generated here -->
                </div>
            </div>
        </main>
    </div>

    <!-- View Inquiry Modal -->
    <div class="modal" id="viewInquiryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Inquiry Details</h3>
                <button class="modal-close" data-modal="viewInquiryModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="inquiry-details">
                    <div class="detail-row">
                        <div class="detail-label">Inquiry ID:</div>
                        <div class="detail-value" id="modalInquiryId">INQ-2023-001</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">From:</div>
                        <div class="detail-value">
                            <div id="modalSenderName">John Doe</div>
                            <div id="modalSenderEmail" style="font-size: 0.9rem; color: var(--gray);">john.doe@email.com</div>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date:</div>
                        <div class="detail-value" id="modalInquiryDate">Nov 25, 2023 10:30 AM</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Category:</div>
                        <div class="detail-value">
                            <span class="badge" id="modalCategory">General Inquiry</span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Priority:</div>
                        <div class="detail-value">
                            <span class="badge" id="modalPriority">Normal</span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">
                            <span class="badge" id="modalStatus">New</span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Subject:</div>
                        <div class="detail-value" id="modalSubject" style="font-weight: 600; color: var(--primary);"></div>
                    </div>
                </div>
                
                <div class="inquiry-message" id="modalMessage">
                    <!-- Inquiry message will be loaded here -->
                </div>
                
                <!-- Reply Form -->
                <div class="reply-form">
                    <h4>Reply to Inquiry</h4>
                    <div class="form-group">
                        <textarea class="form-control" id="replyMessage" placeholder="Type your reply here..." rows="6"></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="sendCopy" checked> Send copy to my email
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="viewInquiryModal">Close</button>
                <button class="btn btn-warning" id="markPendingBtn">Mark as Pending</button>
                <button class="btn btn-success" id="markResolvedBtn">Mark as Resolved</button>
                <button class="btn btn-primary" id="sendReplyBtn">
                    <i class="fas fa-paper-plane"></i> Send Reply
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Modal -->
    <div class="modal" id="bulkActionsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Bulk Actions</h3>
                <button class="modal-close" data-modal="bulkActionsModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Action</label>
                    <select class="form-control" id="bulkAction">
                        <option value="">Choose an action...</option>
                        <option value="mark_read">Mark as Read</option>
                        <option value="mark_pending">Mark as Pending</option>
                        <option value="mark_resolved">Mark as Resolved</option>
                        <option value="change_priority">Change Priority</option>
                        <option value="assign_to">Assign to Team Member</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                </div>
                
                <div id="actionOptions" style="display: none; margin-top: 20px;">
                    <!-- Additional options will appear here based on selected action -->
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Selected Inquiries</label>
                    <div id="selectedCount" style="padding: 10px; background-color: var(--light); border-radius: var(--border-radius);">
                        0 inquiries selected
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="bulkActionsModal">Cancel</button>
                <button class="btn btn-primary" id="applyBulkAction" disabled>Apply Action</button>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; max-width: 350px;"></div>

    <script>
const API_LIST = "api/get_inquiries.php";
const API_ONE  = "api/get_inquiry.php";
const API_REPLY = "api/reply_inquiry.php";
const API_MARK_ALL = "api/mark_all_read.php";

const inquiriesList = document.getElementById('inquiriesList');
const noResults = document.getElementById('noResults');

const totalInquiries = document.getElementById('totalInquiries');
const newInquiries = document.getElementById('newInquiries');
const pendingInquiries = document.getElementById('pendingInquiries');
const resolvedInquiries = document.getElementById('resolvedInquiries');

const allCount = document.getElementById('allCount');
const newCount = document.getElementById('newCount');
const pendingCount = document.getElementById('pendingCount');
const resolvedCount = document.getElementById('resolvedCount');
const highCount = document.getElementById('highCount');

const searchInquiries = document.getElementById('searchInquiries');
const categoryFilter = document.getElementById('categoryFilter');
const dateFrom = document.getElementById('dateFrom');
const dateTo = document.getElementById('dateTo');

const clearFilters = document.getElementById('clearFilters');
const refreshInquiries = document.getElementById('refreshInquiries');
const markAllReadBtn = document.getElementById('markAllReadBtn');

const viewInquiryModal = document.getElementById('viewInquiryModal');
const replyMessage = document.getElementById('replyMessage');
const sendReplyBtn = document.getElementById('sendReplyBtn');
const markPendingBtn = document.getElementById('markPendingBtn');
const markResolvedBtn = document.getElementById('markResolvedBtn');

let allInquiriesData = [];
let activeTabStatus = 'all';
let currentInquiryId = null;

function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}

function cap(s){ return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

function badgeClassByStatus(status){
  if(status === 'new') return 'badge-new';
  if(status === 'pending') return 'badge-pending';
  if(status === 'resolved') return 'badge-resolved';
  return 'badge-normal';
}
function badgeClassByPriority(priority){
  if(priority === 'high') return 'badge-high';
  return 'badge-normal';
}

function updateStats(list){
  const total = list.length;
  const n = list.filter(x => x.status === 'new').length;
  const p = list.filter(x => x.status === 'pending').length;
  const r = list.filter(x => x.status === 'resolved').length;
  const h = list.filter(x => x.priority === 'high').length;

  totalInquiries.textContent = total;
  newInquiries.textContent = n;
  pendingInquiries.textContent = p;
  resolvedInquiries.textContent = r;

  allCount.textContent = total;
  newCount.textContent = n;
  pendingCount.textContent = p;
  resolvedCount.textContent = r;
  highCount.textContent = h;
}

function renderList(list){
  inquiriesList.innerHTML = '';

  if(!list.length){
    noResults.style.display = 'block';
    return;
  }
  noResults.style.display = 'none';

  list.forEach(inq => {
    const item = document.createElement('div');
    item.className = 'inquiry-item'; // keep your CSS class usage

    const dateStr = inq.created_at ? new Date(inq.created_at).toLocaleString() : '-';

    item.innerHTML = `
      <div class="inquiry-header" style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;">
        <div>
          <div style="font-weight:700;color:var(--primary)">${esc(inq.client_name)}</div>
          <div style="font-size:0.9rem;color:var(--gray)">${esc(inq.client_email)}</div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:0.85rem;color:var(--gray)">${esc(dateStr)}</div>
          <div style="margin-top:6px;display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
            <span class="badge ${badgeClassByStatus(inq.status)}">${cap(inq.status)}</span>
            <span class="badge ${badgeClassByPriority(inq.priority)}">${cap(inq.priority)}</span>
          </div>
        </div>
      </div>

      <div class="inquiry-body" style="margin-top:10px;">
        <div style="font-weight:600">${esc(inq.subject)}</div>
        <div style="color:var(--gray);margin-top:6px;line-height:1.4">
          ${esc((inq.message || '').slice(0,140))}${(inq.message || '').length > 140 ? '...' : ''}
        </div>
      </div>

      <div class="inquiry-actions" style="margin-top:12px;display:flex;justify-content:flex-end;gap:10px;">
        <button class="btn btn-secondary" data-action="view" data-id="${esc(inq.id)}">
          <i class="fas fa-eye"></i> View
        </button>
      </div>
    `;

    inquiriesList.appendChild(item);
  });
}

function applyFilters(){
  const q = (searchInquiries.value || '').trim().toLowerCase();
  const cat = (categoryFilter.value || '').trim().toLowerCase();
  const from = dateFrom.value ? new Date(dateFrom.value + 'T00:00:00') : null;
  const to = dateTo.value ? new Date(dateTo.value + 'T23:59:59') : null;

  let list = allInquiriesData.slice();

  // Tabs filter
  if(activeTabStatus !== 'all'){
    if(activeTabStatus === 'high'){
      list = list.filter(x => x.priority === 'high');
    } else {
      list = list.filter(x => x.status === activeTabStatus);
    }
  }

  // Search
  if(q){
    list = list.filter(x => {
      const hay = (x.client_name + ' ' + x.client_email + ' ' + x.subject + ' ' + x.message).toLowerCase();
      return hay.includes(q);
    });
  }

  // Category/type
  if(cat){
    list = list.filter(x => (x.type || '').toLowerCase() === cat);
  }

  // Date range
  if(from){
    list = list.filter(x => x.created_at && new Date(x.created_at) >= from);
  }
  if(to){
    list = list.filter(x => x.created_at && new Date(x.created_at) <= to);
  }

  renderList(list);
}

async function loadInquiries(){
  const res = await fetch(API_LIST);
  const data = await res.json();

  if(!data.success){
    allInquiriesData = [];
    updateStats([]);
    renderList([]);
    return;
  }

  allInquiriesData = data.inquiries || [];
  updateStats(allInquiriesData);
  applyFilters();
}

async function openInquiryModal(id){
  const res = await fetch(`${API_ONE}?id=${encodeURIComponent(id)}`);
  const data = await res.json();
  if(!data.success){
    alert(data.message || 'Failed to load inquiry');
    return;
  }

  const inq = data.inquiry;
  currentInquiryId = inq.id;

  document.getElementById('modalInquiryId').textContent = 'INQ-' + String(inq.id).padStart(4,'0');
  document.getElementById('modalSenderName').textContent = inq.client_name;
  document.getElementById('modalSenderEmail').textContent = inq.client_email;
  document.getElementById('modalInquiryDate').textContent = inq.created_at ? new Date(inq.created_at).toLocaleString() : '-';

  document.getElementById('modalCategory').textContent = cap(inq.type || 'general');
  document.getElementById('modalPriority').textContent = cap(inq.priority || 'medium');
  document.getElementById('modalStatus').textContent = cap(inq.status || 'new');

  document.getElementById('modalSubject').textContent = inq.subject || '';
  document.getElementById('modalMessage').textContent = inq.message || '';

  replyMessage.value = inq.reply_message || '';

  viewInquiryModal.style.display = 'block';
}

async function saveReply(newStatus){
  if(!currentInquiryId) return;

  const msg = replyMessage.value.trim();
  if(!msg) return alert('Please type your reply.');

  // priority from modal badge text (simple rule)
  const priorityText = (document.getElementById('modalPriority').textContent || 'Medium').toLowerCase();
  const pr = (priorityText === 'high' || priorityText === 'low') ? priorityText : 'medium';

  const form = new FormData();
  form.append('id', currentInquiryId);
  form.append('response', msg);
  form.append('status', newStatus);   // pending / resolved
  form.append('priority', pr);

  const res = await fetch(API_REPLY, { method:'POST', body: form });
  const data = await res.json();

  alert(data.message || 'Done');

  if(data.success){
    viewInquiryModal.style.display = 'none';
    currentInquiryId = null;
    await loadInquiries();
  }
}

/* ====== EVENTS ====== */

// Tabs
document.querySelectorAll('.tab').forEach(t => {
  t.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    activeTabStatus = t.dataset.status || 'all';
    applyFilters();
  });
});

// View click from list
inquiriesList.addEventListener('click', (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;
  if(btn.dataset.action === 'view'){
    openInquiryModal(btn.dataset.id);
  }
});

// Modal close buttons (keep your data-modal system)
document.querySelectorAll('.modal-close').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.getAttribute('data-modal');
    const modal = document.getElementById(id);
    if(modal) modal.style.display = 'none';
  });
});
document.querySelectorAll('[data-modal]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.getAttribute('data-modal');
    const modal = document.getElementById(id);
    if(modal) modal.style.display = 'none';
  });
});

// Mark buttons in modal
markPendingBtn.addEventListener('click', ()=> saveReply('pending'));
markResolvedBtn.addEventListener('click', ()=> saveReply('resolved'));
sendReplyBtn.addEventListener('click', ()=> saveReply('pending')); // send -> pending default

// Search/filter
searchInquiries.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', applyFilters);
dateFrom.addEventListener('change', applyFilters);
dateTo.addEventListener('change', applyFilters);

clearFilters.addEventListener('click', ()=>{
  searchInquiries.value = '';
  categoryFilter.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  applyFilters();
});

refreshInquiries.addEventListener('click', loadInquiries);

markAllReadBtn.addEventListener('click', async ()=>{
  const res = await fetch(API_MARK_ALL);
  const data = await res.json();
  alert(data.message || 'Done');
  loadInquiries();
});

// Logout (don’t change your id)
document.getElementById('logoutBtn').addEventListener('click', ()=>{
  if(confirm('Are you sure you want to logout?')){
    window.location.href = '../officer_logout.php';
  }
});

// Start
loadInquiries();
</script>

</body>
</html>