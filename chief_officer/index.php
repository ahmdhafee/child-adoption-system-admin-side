<?php
declare(strict_types=1);

require_once '../officer_auth.php';
require_once '../officer_db.php'; // provides $pdo

if (!isset($_SESSION['officer_role']) || $_SESSION['officer_role'] !== 'chief') {
    http_response_code(403);
    die('Access denied');
}

/**
 * Dashboard stats from your DB tables:
 * - Total Couples = users table count
 * - Pending Approvals = applications where status in (pending, under_review)
 * - Available Children = children where status = available
 * - New Inquiries = (you don't have inquiries table in shared dump) -> use unread user_activities as "inquiries" placeholder
 */

try {
    $totalCouples = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    $pendingApprovals = (int)$pdo->query("
        SELECT COUNT(*)
        FROM applications
        WHERE status IN ('pending','under_review')
    ")->fetchColumn();

    $availableChildren = (int)$pdo->query("
        SELECT COUNT(*)
        FROM children
        WHERE status = 'available'
    ")->fetchColumn();

    // You don't have inquiries table in the dump.
    // We'll show unread activities as "new inquiries" for now (you can replace later).
    $newInquiries = (int)$pdo->query("
        SELECT COUNT(*)
        FROM user_activities
        WHERE is_read = 0
    ")->fetchColumn();

    // Recent Activity (last 8 user activities)
    $activityStmt = $pdo->query("
        SELECT activity_type, title, message, created_at
        FROM user_activities
        ORDER BY created_at DESC
        LIMIT 8
    ");
    $recentActivities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Pending approvals list (top 8)
    $pendingStmt = $pdo->query("
        SELECT a.id, a.registration_id, a.partner1_name, a.partner2_name, a.status, a.created_at
        FROM applications a
        WHERE a.status IN ('pending','under_review')
        ORDER BY a.created_at DESC
        LIMIT 8
    ");
    $pendingList = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Chief dashboard error: " . $e->getMessage());
    $totalCouples = 0;
    $pendingApprovals = 0;
    $availableChildren = 0;
    $newInquiries = 0;
    $recentActivities = [];
    $pendingList = [];
}

function statusBadgeClass(string $status): string {
    return match ($status) {
        'pending' => 'status-pending',
        'under_review' => 'status-inreview',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        default => 'status-pending'
    };
}

// Convert DB status to friendly label for your table
function statusLabel(string $status): string {
    return match ($status) {
        'pending' => 'Pending',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default => ucfirst($status)
    };
}

// map activity type to icon style classes you used
function activityIconClass(string $type): string {
    return match ($type) {
        'success' => 'approval',
        'appointment' => 'profile',
        'inquiry' => 'inquiry',
        'info' => 'register',
        'profile' => 'profile',
        default => 'register'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chief Officer Dashboard | Family Bridge Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
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
                <div class="admin-role">Chief Officer</div>
            </div>

            <nav class="sidebar-nav">
                <!-- IMPORTANT: use .php pages -->
                <a href="index.php" class="nav-item active">
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
                    <span class="badge"><?php echo (int)$pendingApprovals; ?></span>
                </a>
                <a href="inquiries.php" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Inquiries</span>
                    <span class="badge" id="inquiryBadge"><?php echo (int)$newInquiries; ?></span>
                </a>
                <a href="guidelines.php" class="nav-item">
                    <i class="fas fa-book"></i>
                    <span>Guidelines</span>
                </a>
            </nav>

            <div class="logout-section">
                <!-- Real logout -->
                <a class="logout-btn" href="../officer_logout.php" style="text-decoration:none;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
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
                        <h1>Chief Officer Dashboard</h1>
                        <p>Welcome back. Here's what's happening with your adoption portal.</p>
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
                            <input type="text" placeholder="Search records...">
                        </div>
                        <button class="notification-btn" type="button">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge"><?php echo (int)$newInquiries; ?></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <!-- Welcome Section -->
                <section class="welcome-section">
                    <h2>Welcome to Chief Officer Panel</h2>
                    <p>Manage children profiles, handle inquiries, view clients, and schedule appointments from this dashboard.</p>
                    <div style="display: flex; gap: 15px; margin-top: 25px;">
                        <a class="btn btn-secondary" href="children-management.php">
                            <i class="fas fa-plus-circle"></i> Add New Child
                        </a>
                        <a class="btn btn-success" href="reports.php">
                            <i class="fas fa-chart-line"></i> View Reports
                        </a>
                    </div>
                </section>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card total">
                        <div class="stat-icon"><i class="fas fa-user-friends"></i></div>
                        <div class="stat-value"><?php echo $totalCouples; ?></div>
                        <div class="stat-label">Total Couples</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i> Live count</div>
                    </div>

                    <div class="stat-card pending">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-value"><?php echo $pendingApprovals; ?></div>
                        <div class="stat-label">Pending Approvals</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-down"></i> Live count</div>
                    </div>

                    <div class="stat-card children">
                        <div class="stat-icon"><i class="fas fa-child"></i></div>
                        <div class="stat-value"><?php echo $availableChildren; ?></div>
                        <div class="stat-label">Available Children</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i> Live count</div>
                    </div>

                    <div class="stat-card inquiries">
                        <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
                        <div class="stat-value"><?php echo $newInquiries; ?></div>
                        <div class="stat-label">New Inquiries</div>
                        <div class="stat-change negative"><i class="fas fa-arrow-up"></i> Live count</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions-section">
                    <div class="section-header"><h2>Quick Actions</h2></div>
                    <div class="quick-actions-grid">
                        <a href="users.php" class="action-card">
                            <div class="action-icon"><i class="fas fa-user-plus"></i></div>
                            <h3>Add New User</h3>
                            <p>Create new administrator accounts</p>
                        </a>

                        <a href="children-management.php" class="action-card">
                            <div class="action-icon"><i class="fas fa-baby"></i></div>
                            <h3>Add Child Profile</h3>
                            <p>Add new children to the adoption system</p>
                        </a>

                        <a href="appointments.php" class="action-card">
                            <div class="action-icon"><i class="fas fa-calendar-plus"></i></div>
                            <h3>Schedule Meeting</h3>
                            <p>Arrange appointments with clients</p>
                        </a>

                        <a href="guidelines.php" class="action-card">
                            <div class="action-icon"><i class="fas fa-edit"></i></div>
                            <h3>Update Guidelines</h3>
                            <p>Edit adoption policies and guidelines</p>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Recent Activity</h2>
                        <a href="activity.php" class="view-all">View All</a>
                    </div>
                    <div class="card-body">
                        <ul class="activity-list">
                            <?php if (!$recentActivities): ?>
                                <li class="activity-item">
                                    <div class="activity-content">
                                        <div class="activity-title">No activity found</div>
                                        <div class="activity-desc">No records available.</div>
                                    </div>
                                </li>
                            <?php else: foreach ($recentActivities as $a): ?>
                                <li class="activity-item">
                                    <div class="activity-icon <?php echo htmlspecialchars(activityIconClass($a['activity_type'])); ?>">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                        <div class="activity-desc"><?php echo htmlspecialchars($a['message']); ?></div>
                                        <div class="activity-time"><?php echo htmlspecialchars($a['created_at']); ?></div>
                                    </div>
                                </li>
                            <?php endforeach; endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Pending Approvals Requiring Action</h2>
                        <a href="users.php?filter=pending" class="view-all">View All Pending</a>
                    </div>
                    <div class="card-body">
                        <table class="approvals-table">
                            <thead>
                                <tr>
                                    <th>Couple Name</th>
                                    <th>Application ID</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$pendingList): ?>
                                <tr><td colspan="5">No pending applications found</td></tr>
                            <?php else: foreach ($pendingList as $p): ?>
                                <tr>
                                    <td>
                                        <div class="couple-info">
                                            <div class="couple-avatar"><i class="fas fa-users"></i></div>
                                            <div><?php echo htmlspecialchars(($p['partner1_name'] ?? '')); ?> & <?php echo htmlspecialchars(($p['partner2_name'] ?? '')); ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['registration_id']); ?></td>
                                    <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo htmlspecialchars(statusBadgeClass($p['status'])); ?>">
                                            <?php echo htmlspecialchars(statusLabel($p['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a class="btn btn-sm btn-secondary btn-view" href="application_view.php?id=<?php echo (int)$p['id']; ?>">View</a>
                                            <a class="btn btn-sm btn-success btn-approve" href="application_view.php?id=<?php echo (int)$p['id']; ?>#decision">Decide</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- No sessionStorage logout now -->
</body>
</html>
