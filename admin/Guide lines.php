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
    <title>Guidelines | Family Bridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
    <link rel="stylesheet" href="css/Guide lines.css">
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
                        <div class="admin-tag">Guidelines Management</div>
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
                <a href="Guide lines.html" class="nav-item active">
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
                        <h1>Guidelines Management</h1>
                        <p>Create and manage adoption guidelines, policies, and procedures</p>
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
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-number" id="totalGuidelines">28</div>
                        <div class="stat-label">Total Guidelines</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-published">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number" id="publishedGuidelines">22</div>
                        <div class="stat-label">Published</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-draft">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="stat-number" id="draftGuidelines">4</div>
                        <div class="stat-label">Drafts</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-categories">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-number" id="totalCategories">8</div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>

                <!-- Search and Actions Bar -->
                <div class="search-bar">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchGuidelines" placeholder="Search guidelines by title, content, or category..." aria-label="Search guidelines">
                    </div>
                    <div>
                        <button class="btn btn-primary" id="addGuidelineBtn" aria-label="Create new guideline">
                            <i class="fas fa-plus"></i> Create New Guideline
                        </button>
                    </div>
                </div>

                <!-- Categories Tabs -->
                <div class="categories-tabs" id="categoriesTabs">
                    <!-- Categories will be loaded dynamically -->
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
                <div id="gridView" class="guidelines-grid">
                    <!-- Guidelines cards will be loaded here -->
                </div>

                <!-- Table View (Hidden by default) -->
                <div id="tableView" class="content-card" style="display: none;">
                    <div class="table-container">
                        <table class="data-table" id="guidelinesTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Views</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="guidelinesTableBody">
                                <!-- Table rows will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="content-card" style="display: none; text-align: center; padding: 50px;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--gray); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--primary); margin-bottom: 10px;">No Guidelines Found</h3>
                    <p style="color: var(--gray);">Try adjusting your search or filter to find what you're looking for.</p>
                    <button class="btn btn-primary" id="createFirstGuideline" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Create Your First Guideline
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Guideline Modal -->
    <div class="modal" id="guidelineModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Create New Guideline</h3>
                <button class="modal-close" data-modal="guidelineModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="guidelineForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Guideline Title</label>
                            <input type="text" class="form-control" id="guidelineTitle" placeholder="e.g., Adoption Eligibility Criteria" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Category</label>
                            <select class="form-control" id="guidelineCategory" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Status</label>
                            <select class="form-control" id="guidelineStatus" required>
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" class="form-control" id="guidelineTags" placeholder="e.g., eligibility, requirements, process">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Short Description</label>
                        <textarea class="form-control" id="guidelineDescription" rows="3" placeholder="Brief description of this guideline..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Content</label>
                        <div class="editor-container">
                            <div id="editor"></div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Effective Date</label>
                            <input type="date" class="form-control" id="guidelineEffectiveDate">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Review Date</label>
                            <input type="date" class="form-control" id="guidelineReviewDate">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Version</label>
                            <input type="text" class="form-control" id="guidelineVersion" value="1.0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="guidelineModal">Cancel</button>
                <button class="btn btn-primary" id="saveGuidelineBtn">
                    <i class="fas fa-save"></i> Save Guideline
                </button>
                <button class="btn btn-accent" id="previewGuidelineBtn">
                    <i class="fas fa-eye"></i> Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Guideline Modal -->
    <div class="modal preview-modal" id="previewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Guideline Preview</h3>
                <button class="modal-close" data-modal="previewModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="guideline-preview" id="guidelinePreviewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="previewModal">Close</button>
                <button class="btn btn-primary" id="printPreviewBtn">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>

    <!-- View Guideline Modal -->
    <div class="modal" id="viewGuidelineModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Guideline Details</h3>
                <button class="modal-close" data-modal="viewGuidelineModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="guidelineDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="viewGuidelineModal">Close</button>
                <button class="btn btn-primary" id="editGuidelineBtn">Edit Guideline</button>
                <button class="btn btn-danger" id="deleteGuidelineBtn">Delete Guideline</button>
            </div>
        </div>
    </div>

    <!-- Manage Categories Modal -->
    <div class="modal" id="categoriesModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Manage Categories</h3>
                <button class="modal-close" data-modal="categoriesModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="categoriesList">
                    <!-- Categories will be loaded here -->
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--light-gray);">
                    <h4 style="margin-bottom: 15px; color: var(--primary);">Add New Category</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Category Name</label>
                            <input type="text" class="form-control" id="newCategoryName" placeholder="e.g., Legal Requirements">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Color</label>
                            <input type="color" class="form-control" id="newCategoryColor" value="#3498DB" style="height: 42px;">
                        </div>
                    </div>
                    <button class="btn btn-success" id="addCategoryBtn">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="categoriesModal">Close</button>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; max-width: 350px;"></div>

   
</body>
</html>