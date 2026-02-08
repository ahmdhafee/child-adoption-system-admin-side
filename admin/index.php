<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php'; // provides $pdo

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}

/**
 * We will keep your same UI IDs:
 * totalClients, totalChildren, totalGuides, pendingInquiries
 * We'll fill them from DB.
 *
 * Note:
 * - "Guidelines count" depends on your table. If guidelines table exists, count active/draft etc.
 * - You currently used "user_activities" as inquiry placeholder.
 */

$totalClients = 0;
$totalChildren = 0;
$totalGuides = 0;
$pendingInquiries = 0;

try {
    $totalClients = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalChildren = (int)$pdo->query("SELECT COUNT(*) FROM children")->fetchColumn();

    // Guidelines table check (avoid crash if table not created)
    $guidelinesTableExists = (int)$pdo->query("
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'guidelines'
    ")->fetchColumn();

    if ($guidelinesTableExists > 0) {
        // If you have status column, prefer active. If not, just count all.
        $hasStatusColumn = (int)$pdo->query("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'guidelines'
              AND column_name = 'status'
        ")->fetchColumn();

        if ($hasStatusColumn > 0) {
            $totalGuides = (int)$pdo->query("
                SELECT COUNT(*)
                FROM guidelines
                WHERE status IN ('active','draft')
            ")->fetchColumn();
        } else {
            $totalGuides = (int)$pdo->query("SELECT COUNT(*) FROM guidelines")->fetchColumn();
        }
    } else {
        $totalGuides = 0;
    }

    // Inquiries placeholder using unread activities
    $pendingInquiries = (int)$pdo->query("
        SELECT COUNT(*)
        FROM user_activities
        WHERE is_read = 0
    ")->fetchColumn();

} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    // keep defaults
}

$adminName = $_SESSION['officer_name'] ?? 'Admin User';
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
    <link rel="shortcut icon" href="../favlogo.png" type="image/png">

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
                <i class="fas fa-hands-helping"></i>
                <div>
                    <div style="font-size: 1.3rem;">Family Bridge</div>
                    <div >Admin Panel</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <!-- keep class names: nav-item active -->
            <a href="index.php" class="nav-item active">
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
            <a href="documents_review.php" class="nav-item">
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
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back! Here's what's happening today.</p>
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
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h2>Welcome to Admin Panel</h2>
                <p>Manage children profiles, handle inquiries, view clients, and schedule appointments from this dashboard.</p>
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                   
                </div>
            </section>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon clients">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <!-- keep id totalClients -->
                        <h3 id="totalClients"><?php echo (int)$totalClients; ?></h3>
                        <p>Total Clients</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon children">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="stat-info">
                        <!-- keep id totalChildren -->
                        <h3 id="totalChildren"><?php echo (int)$totalChildren; ?></h3>
                        <p>Children in System</p>
                    </div>
                </div>

                

                <div class="stat-card">
                    <div class="stat-icon inquiries">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="stat-info">
                        <!-- keep id pendingInquiries -->
                        <h3 id="pendingInquiries"><?php echo (int)$pendingInquiries; ?></h3>
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
                    <h3>View Children</h3>
                    <p>See the child profiles from the system.</p>
                    <a href="children-management.php" class="btn btn-primary btn-block">
                        <i class="fas fa-cog"></i> View Children
                    </a>
                </div>

               

                <div class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3>Handle Inquiries</h3>
                    <p>Respond to client questions and support requests.</p>
                    <a href="inquiries.php" class="btn btn-accent btn-block">
                        <i class="fas fa-comments"></i> View Inquiries
                    </a>
                </div>
            </div>

            <!-- Recent Activity & Calendar -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                    <div class="card-actions">
                        <button class="btn btn-secondary" id="refreshActivity" type="button">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                        <!-- Activity Timeline -->
                        <div>
                            <h3 style="margin-bottom: 20px; color: var(--primary);">Recent Activity</h3>
                            <!-- keep id activityTimeline -->
                            <div class="activity-timeline" id="activityTimeline">
                                <!-- You can load via JS later, or we can render PHP list here -->
                                <div style="color: var(--gray);">Activity feed will load here.</div>
                            </div>
                        </div>

                        
                        
                    </div>
                </div>
            </div>

           

        </div>
    </main>
</div>

<!-- Notifications Container -->
<div class="notifications-container" id="notificationsContainer"></div>

<script>
  // IMPORTANT:
  // You asked not to change IDs/classes.
  // We kept logoutBtn id, but logout is now a real link (server session logout).
  // No sessionStorage used.
</script>
</body>
</html>
