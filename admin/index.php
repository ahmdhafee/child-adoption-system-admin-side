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
    <title>Admin Dashboard | Family Bridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="shortcut icon" href="favlogo.png" type="logo">
    <style>
        /* CSS Reset and Base Styles */
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
                    <!-- Replace with your logo -->
                    <i class="fas fa-hands-helping"></i>
                    <div>
                        <div style="font-size: 1.3rem;">Family Bridge</div>
                        <div class="admin-tag">Admin Panel</div>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.html" class="nav-item active">
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
                    <span class="badge">12</span>
                </a>
                <a href="Inquires.html" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    Inquiries
                    <span class="badge" id="inquiryBadge">5</span>
                </a>
                <a href="Clients.html" class="nav-item">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
                <a href="Appointments.html" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                    <span class="badge">3</span>
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
                        <h1>Admin Dashboard</h1>
                        <p>Welcome back! Here's what's happening today.</p>
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
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <h2>Welcome to Admin Panel</h2>
                    <p>Manage children profiles, handle inquiries, view clients, and schedule appointments from this dashboard.</p>
                    <div style="display: flex; gap: 15px; margin-top: 25px;">
                        <button class="btn btn-secondary" id="quickAddChild">
                            <i class="fas fa-plus-circle"></i> Add New Child
                        </button>
                        <button class="btn btn-accent" id="viewReports">
                            <i class="fas fa-chart-line"></i> View Reports
                        </button>
                    </div>
                </section>

                <!-- Quick Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon clients">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalClients">156</h3>
                            <p>Total Clients</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon children">
                            <i class="fas fa-child"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalChildren">48</h3>
                            <p>Children in System</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon guides">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="totalGuides">12</h3>
                            <p>Active Guidelines</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon inquiries">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3 id="pendingInquiries">5</h3>
                            <p>Pending Inquiries</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="actions-grid">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-child"></i>
                        </div>
                        <h3>Manage Children</h3>
                        <p>Add, edit, or remove child profiles from the system.</p>
                        <a href="children-management.html" class="btn btn-primary btn-block">
                            <i class="fas fa-cog"></i> Manage Children
                        </a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Guidelines</h3>
                        <p>Create and manage adoption guidelines and policies.</p>
                        <a href="Guide lines.html" class="btn btn-secondary btn-block">
                            <i class="fas fa-edit"></i> Manage Guidelines
                        </a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3>Handle Inquiries</h3>
                        <p>Respond to client questions and support requests.</p>
                        <a href="Inquires.html" class="btn btn-accent btn-block">
                            <i class="fas fa-comments"></i> View Inquiries
                        </a>
                    </div>
                </div>

                <!-- Recent Activity & Calendar -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Recent Activity & Calendar</h2>
                        <div class="card-actions">
                            <button class="btn btn-secondary" id="refreshActivity">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                            <!-- Activity Timeline -->
                            <div>
                                <h3 style="margin-bottom: 20px; color: var(--primary);">Recent Activity</h3>
                                <div class="activity-timeline" id="activityTimeline">
                                    <!-- Activities will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Calendar Widget -->
                            <div>
                                <h3 style="margin-bottom: 20px; color: var(--primary);">Upcoming Events</h3>
                                <div class="calendar-widget">
                                    <div class="calendar-header">
                                        <h3>December 2023</h3>
                                        <button class="btn btn-secondary btn-sm" id="prevMonth">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                    </div>
                                    <div class="calendar-grid" id="calendarGrid">
                                        <!-- Calendar will be generated here -->
                                    </div>
                                    <div id="upcomingEvents">
                                        <!-- Upcoming events will be shown here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>System Status</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div>
                                <h3 style="color: var(--dark); margin-bottom: 10px;">Database Status</h3>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="flex: 1; height: 8px; background-color: var(--light-gray); border-radius: 4px;">
                                        <div style="width: 65%; height: 100%; background-color: var(--success); border-radius: 4px;"></div>
                                    </div>
                                    <span>65% Capacity</span>
                                </div>
                            </div>
                            
                            <div>
                                <h3 style="color: var(--dark); margin-bottom: 10px;">Server Uptime</h3>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-server" style="color: var(--success);"></i>
                                    <span>99.8% (30 days)</span>
                                </div>
                            </div>
                            
                            <div>
                                <h3 style="color: var(--dark); margin-bottom: 10px;">Last Backup</h3>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-database" style="color: var(--info);"></i>
                                    <span>Dec 1, 2023 02:00 AM</span>
                                </div>
                            </div>
                            
                            <div>
                                <h3 style="color: var(--dark); margin-bottom: 10px;">Security Status</h3>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-shield-alt" style="color: var(--success);"></i>
                                    <span>All Systems Secure</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" id="notificationsContainer">
        <!-- Notifications will appear here -->
    </div>

    
    
    <script>
        const logoutBtn = document.getElementById('logoutBtn');
            
            
            function logoutUser() {
                if (confirm('Are you sure you want to logout?')) {
                    sessionStorage.removeItem('adminLoggedIn');
                    sessionStorage.removeItem('loggedIn');
                    sessionStorage.removeItem('userRole');
                    sessionStorage.removeItem('userEmail');
                    window.location.href = '../officeLogin.html';
                }
            }

            if (logoutBtn) logoutBtn.addEventListener('click', logoutUser);
            if (headerLogoutBtn) headerLogoutBtn.addEventListener('click', logoutUser);
    </script>
</body>
</html>