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
    <title>Clients | Family Bridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/Clients.css">
    <link rel="shortcut icon" href="favlogo.png" type="logo">
    <style>
        /* Base Styles - Same as guidelines page */
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
                        <div class="admin-tag">Clients Management</div>
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
                <a href="guidelines.html" class="nav-item">
                    <i class="fas fa-book"></i>
                    Guidelines
                </a>
                <a href="inquires.html" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    Inquiries
                </a>
                <a href="clients.html" class="nav-item active">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
                <a href="appointments.html" class="nav-item">
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
                        <h1>Clients Management</h1>
                        <p>Manage prospective adoptive families and their profiles</p>
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar">AD</div>
                        <div class="admin-info">
                            <h4>Admin User</h4>
                            <p>System Administrator</p>
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
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" id="totalClients">0</div>
                        <div class="stat-label">Total Clients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-active">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-number" id="activeClients">0</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-pending">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div class="stat-number" id="pendingClients">0</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-approved">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-number" id="approvedClients">0</div>
                        <div class="stat-label">Approved Families</div>
                    </div>
                </div>

                <!-- Search and Filter Bar -->
                <div class="search-filter-bar">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchClients" placeholder="Search clients by name, email, or phone..." aria-label="Search clients">
                    </div>
                    
                    <div class="filter-select">
                        <select id="filterStatus">
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="filter-select">
                        <select id="filterStage">
                            <option value="all">All Stages</option>
                            <option value="inquiry">Initial Inquiry</option>
                            <option value="application">Application</option>
                            <option value="home-study">Home Study</option>
                            <option value="matching">Matching</option>
                            <option value="post-placement">Post-Placement</option>
                            <option value="finalized">Adoption Finalized</option>
                        </select>
                    </div>
                    
                    <div>
                        <button class="btn btn-primary" id="addClientBtn">
                            <i class="fas fa-user-plus"></i> Add New Client
                        </button>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle">
                    <button class="view-toggle-btn active" data-view="grid">
                        <i class="fas fa-th-large"></i> Grid View
                    </button>
                    <button class="view-toggle-btn" data-view="table">
                        <i class="fas fa-table"></i> Table View
                    </button>
                </div>

                <!-- Grid View -->
                <div id="gridView" class="clients-grid">
                    <!-- Client cards will be loaded here -->
                </div>

                <!-- Table View (Hidden by default) -->
                <div id="tableView" class="content-card" style="display: none;">
                    <div class="table-container">
                        <table class="data-table" id="clientsTable">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Contact Info</th>
                                    <th>Status</th>
                                    <th>Stage</th>
                                    <th>Registration Date</th>
                                    <th>Last Contact</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="clientsTableBody">
                                <!-- Table rows will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="content-card" style="display: none; text-align: center; padding: 50px;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--gray); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--primary); margin-bottom: 10px;">No Clients Found</h3>
                    <p style="color: var(--gray);">Try adjusting your search or filter to find what you're looking for.</p>
                    <button class="btn btn-primary" id="createFirstClient" style="margin-top: 20px;">
                        <i class="fas fa-user-plus"></i> Add Your First Client
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Client Modal -->
    <div class="modal" id="clientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Client</h3>
                <button class="modal-close" data-modal="clientModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="tabs" id="clientTabs">
                    <button class="tab active" data-tab="personal">Personal Info</button>
                    <button class="tab" data-tab="family">Family Details</button>
                    <button class="tab" data-tab="adoption">Adoption Info</button>
                    <button class="tab" data-tab="documents">Documents</button>
                </div>
                
                <form id="clientForm">
                    <!-- Personal Information Tab -->
                    <div class="tab-content active" id="personalTab">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label required">First Name</label>
                                <input type="text" class="form-control" id="firstName" placeholder="John" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Last Name</label>
                                <input type="text" class="form-control" id="lastName" placeholder="Doe" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="john.doe@example.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Phone</label>
                                <input type="tel" class="form-control" id="phone" placeholder="(123) 456-7890" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <select class="form-control" id="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                    <option value="prefer-not-to-say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Address</label>
                            <textarea class="form-control" id="address" rows="2" placeholder="123 Main St, City, State, ZIP" required></textarea>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" id="occupation" placeholder="Software Engineer">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Employer</label>
                                <input type="text" class="form-control" id="employer" placeholder="Tech Company Inc.">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Family Details Tab -->
                    <div class="tab-content" id="familyTab">
                        <div class="form-group">
                            <label class="form-label">Marital Status</label>
                            <select class="form-control" id="maritalStatus">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                                <option value="separated">Separated</option>
                                <option value="domestic-partnership">Domestic Partnership</option>
                            </select>
                        </div>
                        
                        <!-- Spouse Information (only show if married or domestic partnership) -->
                        <div id="spouseSection" style="display: none;">
                            <h4 style="margin: 25px 0 15px; color: var(--primary);">Spouse Information</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Spouse First Name</label>
                                    <input type="text" class="form-control" id="spouseFirstName" placeholder="Jane">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Spouse Last Name</label>
                                    <input type="text" class="form-control" id="spouseLastName" placeholder="Doe">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Spouse Email</label>
                                    <input type="email" class="form-control" id="spouseEmail" placeholder="jane.doe@example.com">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Spouse Phone</label>
                                    <input type="tel" class="form-control" id="spousePhone" placeholder="(987) 654-3210">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Spouse Date of Birth</label>
                                    <input type="date" class="form-control" id="spouseDob">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Spouse Occupation</label>
                                    <input type="text" class="form-control" id="spouseOccupation" placeholder="Teacher">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Family Members -->
                        <div class="form-group">
                            <label class="form-label">Children in Home (Biological/Adopted)</label>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Number of Children</label>
                                    <input type="number" class="form-control" id="childrenCount" min="0" value="0">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Ages of Children</label>
                                    <input type="text" class="form-control" id="childrenAges" placeholder="e.g., 5, 8, 12">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Preferred Age Range for Adoption</label>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Minimum Age</label>
                                    <input type="number" class="form-control" id="minAge" min="0" max="18" placeholder="0">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Maximum Age</label>
                                    <input type="number" class="form-control" id="maxAge" min="0" max="18" placeholder="18">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Special Needs Willingness</label>
                            <select class="form-control" id="specialNeeds">
                                <option value="">Select Preference</option>
                                <option value="open">Open to all special needs</option>
                                <option value="mild">Mild special needs only</option>
                                <option value="specific">Specific special needs</option>
                                <option value="none">No special needs</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Adoption Info Tab -->
                    <div class="tab-content" id="adoptionTab">
                        <div class="form-group">
                            <label class="form-label required">Application Status</label>
                            <select class="form-control" id="applicationStatus" required>
                                <option value="inquiry">Initial Inquiry</option>
                                <option value="application">Application Submitted</option>
                                <option value="home-study">Home Study in Progress</option>
                                <option value="matching">Matching Phase</option>
                                <option value="post-placement">Post-Placement</option>
                                <option value="finalized">Adoption Finalized</option>
                            </select>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Application Date</label>
                                <input type="date" class="form-control" id="applicationDate">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Home Study Date</label>
                                <input type="date" class="form-control" id="homeStudyDate">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Adoption Preferences</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                                <label style="display: flex; align-items: center; gap: 5px;">
                                    <input type="checkbox" id="prefDomestic" value="domestic">
                                    Domestic Adoption
                                </label>
                                <label style="display: flex; align-items: center; gap: 5px;">
                                    <input type="checkbox" id="prefInternational" value="international">
                                    International Adoption
                                </label>
                                <label style="display: flex; align-items: center; gap: 5px;">
                                    <input type="checkbox" id="prefFoster" value="foster">
                                    Foster Care Adoption
                                </label>
                                <label style="display: flex; align-items: center; gap: 5px;">
                                    <input type="checkbox" id="prefNewborn" value="newborn">
                                    Newborn Adoption
                                </label>
                                <label style="display: flex; align-items: center; gap: 5px;">
                                    <input type="checkbox" id="prefSiblingGroup" value="sibling-group">
                                    Sibling Group
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" rows="4" placeholder="Additional notes about the client's adoption journey..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Documents Tab -->
                    <div class="tab-content" id="documentsTab">
                        <div class="form-group">
                            <label class="form-label">Document Checklist</label>
                            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docApplication" value="application">
                                    <span>Application Form</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docBirthCertificate" value="birth-certificate">
                                    <span>Birth Certificate(s)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docMarriageCertificate" value="marriage-certificate">
                                    <span>Marriage Certificate (if applicable)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docFinancial" value="financial">
                                    <span>Financial Statements</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docMedical" value="medical">
                                    <span>Medical Reports</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docBackground" value="background">
                                    <span>Background Checks</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docReferences" value="references">
                                    <span>Reference Letters</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="docHomeStudy" value="home-study">
                                    <span>Home Study Report</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Upload New Document</label>
                            <div style="border: 2px dashed var(--light-gray); border-radius: var(--border-radius); padding: 30px; text-align: center;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--gray); margin-bottom: 15px;"></i>
                                <p style="color: var(--gray); margin-bottom: 15px;">Drag & drop files here or click to browse</p>
                                <input type="file" class="form-control" id="documentUpload" multiple style="display: none;">
                                <button type="button" class="btn btn-secondary" id="browseFilesBtn">
                                    <i class="fas fa-folder-open"></i> Browse Files
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="clientModal">Cancel</button>
                <button class="btn btn-primary" id="saveClientBtn">
                    <i class="fas fa-save"></i> Save Client
                </button>
            </div>
        </div>
    </div>

    <!-- View Client Modal -->
    <div class="modal" id="viewClientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Client Profile</h3>
                <button class="modal-close" data-modal="viewClientModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="clientProfileContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="viewClientModal">Close</button>
                <button class="btn btn-primary" id="editClientBtn">Edit Client</button>
                <button class="btn btn-warning" id="sendMessageBtn">
                    <i class="fas fa-envelope"></i> Send Message
                </button>
                <button class="btn btn-danger" id="deleteClientBtn">Delete Client</button>
            </div>
        </div>
    </div>

    <!-- Send Message Modal -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Message to Client</h3>
                <button class="modal-close" data-modal="messageModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="messageForm">
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="text" class="form-control" id="recipient" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Subject</label>
                        <input type="text" class="form-control" id="messageSubject" placeholder="Regarding your adoption application" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Message</label>
                        <textarea class="form-control" id="messageContent" rows="6" placeholder="Type your message here..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message Type</label>
                        <select class="form-control" id="messageType">
                            <option value="general">General Communication</option>
                            <option value="appointment">Appointment Reminder</option>
                            <option value="document-request">Document Request</option>
                            <option value="update">Status Update</option>
                            <option value="follow-up">Follow-up</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="messageModal">Cancel</button>
                <button class="btn btn-primary" id="sendMessageNowBtn">
                    <i class="fas fa-paper-plane"></i> Send Message
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
                        <option value="">Choose an action</option>
                        <option value="export">Export Selected</option>
                        <option value="status-change">Change Status</option>
                        <option value="send-bulk-message">Send Bulk Message</option>
                        <option value="assign-caseworker">Assign Caseworker</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                </div>
                
                <div id="bulkActionOptions" style="display: none;">
                    <!-- Options will be shown based on selected action -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="bulkActionsModal">Cancel</button>
                <button class="btn btn-primary" id="executeBulkActionBtn">
                    Execute Action
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; max-width: 350px;"></div>

    
</body>
</html>