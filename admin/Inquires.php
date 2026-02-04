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
                        <div class="admin-tag">Inquiries Management</div>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.html" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="children-management.html" class="nav-item">
                    <i class="fas fa-child"></i>
                    Children Management
                </a>
                <a href="Guide lines.html" class="nav-item">
                    <i class="fas fa-book"></i>
                    Guidelines
                </a>
                <a href="Inquires.html" class="nav-item active">
                    <i class="fas fa-question-circle"></i>
                    Inquiries
                </a>
                <a href="Clients.html" class="nav-item">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
                <a href="Appointments.html" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </a>
            </nav>
            
            <div class="logout-section">
                <button class="logout-btn" id="logoutBtn"  >
                    <i class="fas fa-sign-out-alt"></i>
                    <span href="../officer_logout.php">Logout</span>
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
                        <p>Manage client questions, support requests, and feedback</p>
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar">AD</div>
                        <div class="admin-info">
                            <h4>Admin User</h4>
                            <p>Support Administrator</p>
                        </div>
                    </div>
                    <button class="logout-btn" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </button>
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

   
</body>
</html>