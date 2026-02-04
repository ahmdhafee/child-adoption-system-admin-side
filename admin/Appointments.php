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
    <title>Appointments | Family Bridge Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/Appointments.css">
    <link rel="shortcut icon" href="favlogo.png" type="logo">
    <style>
        /* Base Styles - Same as other pages */
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
                        <div class="admin-tag">Appointments Management</div>
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
                <a href="clients.html" class="nav-item">
                    <i class="fas fa-users"></i>
                    Clients
                </a>
                <a href="appointments.html" class="nav-item active">
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
                        <h1>Appointments Management</h1>
                        <p>Schedule and manage meetings, consultations, and home visits</p>
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar">AD</div>
                        <div class="admin-info">
                            <h4>Admin User</h4>
                            <p>Appointments Coordinator</p>
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
                        <div class="stat-icon icon-today">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-number" id="todayAppointments">5</div>
                        <div class="stat-label">Today's Appointments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-upcoming">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-number" id="upcomingAppointments">18</div>
                        <div class="stat-label">Upcoming This Week</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number" id="pendingAppointments">3</div>
                        <div class="stat-label">Pending Confirmation</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number" id="completedAppointments">42</div>
                        <div class="stat-label">Completed This Month</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions" style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
                    <button class="btn btn-primary" id="newAppointmentBtn">
                        <i class="fas fa-plus"></i> New Appointment
                    </button>
                    <button class="btn btn-secondary" id="importCalendarBtn">
                        <i class="fas fa-calendar-plus"></i> Import Calendar
                    </button>
                    <button class="btn btn-accent" id="sendRemindersBtn">
                        <i class="fas fa-bell"></i> Send Reminders
                    </button>
                    <button class="btn btn-warning" id="printScheduleBtn">
                        <i class="fas fa-print"></i> Print Schedule
                    </button>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle">
                    <button class="view-toggle-btn active" data-view="calendar">
                        <i class="fas fa-calendar-alt"></i> Calendar View
                    </button>
                    <button class="view-toggle-btn" data-view="list">
                        <i class="fas fa-list"></i> List View
                    </button>
                    <button class="view-toggle-btn" data-view="grid">
                        <i class="fas fa-th-large"></i> Grid View
                    </button>
                </div>

                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active" data-status="all">
                        All Appointments
                    </button>
                    <button class="tab" data-status="today">
                        Today
                    </button>
                    <button class="tab" data-status="upcoming">
                        Upcoming
                    </button>
                    <button class="tab" data-status="pending">
                        Pending
                    </button>
                    <button class="tab" data-status="completed">
                        Completed
                    </button>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchAppointments" placeholder="Search by client name, type, or notes...">
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Type</label>
                        <select class="form-control" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="consultation">Consultation</option>
                            <option value="home-study">Home Study</option>
                            <option value="interview">Interview</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="training">Training</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="form-label">Date Range</label>
                        <input type="date" class="form-control" id="dateFrom" value="2023-11-01">
                    </div>
                    <div class="filter-group">
                        <label class="form-label">To</label>
                        <input type="date" class="form-control" id="dateTo" value="2023-11-30">
                    </div>
                    <div class="filter-group">
                        <button class="btn btn-secondary" id="clearFilters">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Calendar View -->
                <div id="calendarView" class="calendar-view fade-in">
                    <div class="calendar-header">
                        <div class="calendar-navigation">
                            <button class="btn btn-sm btn-secondary" id="prevMonth">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="calendar-title" id="currentMonth">November 2023</div>
                            <button class="btn btn-sm btn-secondary" id="nextMonth">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" id="todayBtn">
                                Today
                            </button>
                        </div>
                        <div class="calendar-actions">
                            <button class="btn btn-sm btn-success" id="addEventBtn">
                                <i class="fas fa-plus"></i> Add Event
                            </button>
                        </div>
                    </div>
                    
                    <div class="calendar-days">
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                    </div>
                    
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Calendar cells will be generated here -->
                    </div>
                </div>

                <!-- Grid View (Hidden by default) -->
                <div id="gridView" class="appointments-grid" style="display: none;">
                    <!-- Appointment cards will be loaded here -->
                </div>

                <!-- List View (Hidden by default) -->
                <div id="listView" class="content-card" style="display: none;">
                    <div class="table-container">
                        <table class="data-table" id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Staff</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTableBody">
                                <!-- Table rows will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="no-results" style="display: none;">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Appointments Found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                    <button class="btn btn-primary" id="createFirstAppointment" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Schedule First Appointment
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Appointment Modal -->
    <div class="modal" id="appointmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Schedule New Appointment</h3>
                <button class="modal-close" data-modal="appointmentModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="appointmentForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Appointment Type</label>
                            <select class="form-control" id="appointmentType" required>
                                <option value="">Select Type</option>
                                <option value="consultation">Initial Consultation</option>
                                <option value="home-study">Home Study Visit</option>
                                <option value="interview">Family Interview</option>
                                <option value="follow-up">Follow-up Meeting</option>
                                <option value="training">Adoption Training</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Client</label>
                            <select class="form-control" id="appointmentClient" required>
                                <option value="">Select Client</option>
                                <!-- Clients will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Date</label>
                            <input type="date" class="form-control" id="appointmentDate" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Time</label>
                            <select class="form-control" id="appointmentTime" required>
                                <option value="">Select Time</option>
                                <!-- Times will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">Duration</label>
                            <select class="form-control" id="appointmentDuration" required>
                                <option value="30">30 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                                <option value="180">3 hours</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Staff Member</label>
                            <select class="form-control" id="appointmentStaff" required>
                                <option value="">Select Staff</option>
                                <!-- Staff will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Location</label>
                            <select class="form-control" id="appointmentLocation" required>
                                <option value="">Select Location</option>
                                <option value="office">Main Office</option>
                                <option value="client-home">Client's Home</option>
                                <option value="video">Video Conference</option>
                                <option value="other">Other Location</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Status</label>
                            <select class="form-control" id="appointmentStatus" required>
                                <option value="scheduled">Scheduled</option>
                                <option value="confirmed" selected>Confirmed</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Meeting Link (for video appointments)</label>
                        <input type="text" class="form-control" id="meetingLink" placeholder="https://meet.google.com/abc-defg-hij">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Notes</label>
                        <textarea class="form-control" id="appointmentNotes" rows="4" placeholder="Add notes about this appointment..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="sendNotifications" checked> Send email notifications to client
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="appointmentModal">Cancel</button>
                <button class="btn btn-primary" id="saveAppointmentBtn">
                    <i class="fas fa-save"></i> Save Appointment
                </button>
            </div>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div class="modal" id="viewAppointmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Appointment Details</h3>
                <button class="modal-close" data-modal="viewAppointmentModal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="appointmentDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="viewAppointmentModal">Close</button>
                <button class="btn btn-primary" id="editAppointmentBtn">Edit Appointment</button>
                <button class="btn btn-warning" id="sendReminderBtn">
                    <i class="fas fa-bell"></i> Send Reminder
                </button>
                <button class="btn btn-danger" id="cancelAppointmentBtn">Cancel Appointment</button>
            </div>
        </div>
    </div>

    <!-- Send Reminder Modal -->
    <div class="modal" id="reminderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Appointment Reminder</h3>
                <button class="modal-close" data-modal="reminderModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reminderForm">
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="text" class="form-control" id="reminderRecipient" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Subject</label>
                        <input type="text" class="form-control" id="reminderSubject" value="Appointment Reminder" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Message</label>
                        <textarea class="form-control" id="reminderMessage" rows="6" required>This is a reminder for your upcoming appointment...</textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Reminder Type</label>
                            <select class="form-control" id="reminderType">
                                <option value="24">24 hours before</option>
                                <option value="48" selected>48 hours before</option>
                                <option value="72">72 hours before</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Send Method</label>
                            <select class="form-control" id="reminderMethod">
                                <option value="email" selected>Email Only</option>
                                <option value="sms">SMS Only</option>
                                <option value="both">Email & SMS</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-modal="reminderModal">Cancel</button>
                <button class="btn btn-primary" id="sendReminderNowBtn">
                    <i class="fas fa-paper-plane"></i> Send Reminder
                </button>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; max-width: 350px;"></div>

    
</body>
</html>