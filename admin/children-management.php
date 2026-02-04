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
    <title>Children Management | Family Bridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/children-management.css">
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
                        <div class="admin-tag">Children Management</div>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.html" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="children-management.html" class="nav-item active">
                    <i class="fas fa-child"></i>
                    Children Management
                </a>
                <a href="Guide lines.html" class="nav-item">
                    <i class="fas fa-book"></i>
                    Guidelines
                </a>
                <a href="Inquires.html" class="nav-item">
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
                        <h1>Children Management</h1>
                        <p>Manage child profiles, availability, and adoption status</p>
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
                <!-- Statistics Overview -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-number" id="totalChildrenCount">48</div>
                        <div class="stat-label">Total Children</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="availableChildrenCount">32</div>
                        <div class="stat-label">Available for Adoption</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="reservedChildrenCount">8</div>
                        <div class="stat-label">Reserved</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="adoptedChildrenCount">8</div>
                        <div class="stat-label">Successfully Adopted</div>
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

                <!-- Search and Filter -->
                <div class="search-filter-bar">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label class="form-label">Search Children</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by name, ID, or institution...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="adopted">Adopted</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Age Range</label>
                            <select class="form-control" id="ageFilter">
                                <option value="">All Ages</option>
                                <option value="0-2">0-2 years</option>
                                <option value="3-5">3-5 years</option>
                                <option value="6-10">6-10 years</option>
                                <option value="11+">11+ years</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Institution</label>
                            <select class="form-control" id="institutionFilter">
                                <option value="">All Institutions</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <button class="btn btn-secondary" id="clearFilters">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-primary" id="addChildBtn">
                                <i class="fas fa-plus"></i> Add New Child
                            </button>
                            <button class="btn btn-success" id="exportChildren">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid View -->
                <div id="gridView" class="children-grid">
                    <!-- Children cards will be loaded here -->
                </div>

                <!-- Table View (Hidden by default) -->
                <div id="tableView" class="content-card" style="display: none;">
                    <div class="table-container">
                        <table class="data-table" id="childrenTable">
                            <thead>
                                <tr>
                                    <th>Child ID</th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Institution</th>
                                    <th>Date Added</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="childrenTableBody">
                                <!-- Table rows will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div id="pagination" style="display: flex; justify-content: center; margin-top: 30px;">
                    <!-- Pagination will be generated here -->
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Child Modal -->
    <div class="modal" id="childModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Child</h3>
                <button class="modal-close" data-modal="childModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="childForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" id="childFirstName" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="childMiddleName">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Last Name</label>
                            <input type="text" class="form-control" id="childLastName" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" class="form-control" id="childDOB" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Gender</label>
                            <select class="form-control" id="childGender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Status</label>
                            <select class="form-control" id="childStatus" required>
                                <option value="available">Available for Adoption</option>
                                <option value="reserved">Reserved</option>
                                <option value="adopted">Adopted</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Institution</label>
                            <select class="form-control" id="childInstitution" required>
                                <option value="">Select Institution</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Medical Conditions</label>
                            <textarea class="form-control" id="childMedical" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Special Needs</label>
                            <textarea class="form-control" id="childSpecialNeeds" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Background Information</label>
                            <textarea class="form-control" id="childBackground" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Hobbies & Interests</label>
                            <textarea class="form-control" id="childInterests" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <!-- Photo Upload -->
                    <div class="form-group">
                        <label class="form-label">Child Photo</label>
                        <div class="file-upload" id="photoUpload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload or drag and drop</p>
                            <p style="font-size: 0.8rem; color: var(--gray);">PNG, JPG up to 5MB</p>
                        </div>
                        <input type="file" id="childPhoto" accept="image/*" style="display: none;">
                        <div id="photoPreview" style="margin-top: 15px;"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="childModal">Cancel</button>
                <button class="btn btn-primary" id="saveChildBtn">
                    <i class="fas fa-save"></i> Save Child
                </button>
            </div>
        </div>
    </div>

    <!-- View Child Details Modal -->
    <div class="modal" id="viewChildModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Child Details</h3>
                <button class="modal-close" data-modal="viewChildModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="childDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="viewChildModal">Close</button>
                <button class="btn btn-primary" id="editChildBtn">Edit Child</button>
                <button class="btn btn-danger" id="deleteChildBtn">Delete Child</button>
            </div>
        </div>
    </div>

    <!-- Import Children Modal -->
    <div class="modal" id="importModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Import Children Data</h3>
                <button class="modal-close" data-modal="importModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="file-upload" id="csvUpload">
                    <i class="fas fa-file-csv"></i>
                    <p>Click to upload CSV file or drag and drop</p>
                    <p style="font-size: 0.8rem; color: var(--gray);">CSV format only, up to 10MB</p>
                </div>
                <input type="file" id="csvFile" accept=".csv" style="display: none;">
                
                <div style="margin-top: 30px;">
                    <h4 style="margin-bottom: 15px; color: var(--primary);">CSV Format Requirements:</h4>
                    <div style="background-color: var(--light); padding: 15px; border-radius: var(--border-radius);">
                        <p><strong>Required columns:</strong> First Name, Last Name, Date of Birth, Gender, Institution</p>
                        <p><strong>Optional columns:</strong> Middle Name, Medical Conditions, Special Needs, Background</p>
                        <p><strong>Date format:</strong> YYYY-MM-DD</p>
                        <p><strong>Gender values:</strong> male, female, other</p>
                        <a href="#" id="downloadTemplate" style="color: var(--secondary); text-decoration: none;">
                            <i class="fas fa-download"></i> Download CSV Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="importModal">Cancel</button>
                <button class="btn btn-primary" id="importCSVBtn" disabled>
                    <i class="fas fa-upload"></i> Import Data
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; max-width: 350px;"></div>

   
</body>
</html>