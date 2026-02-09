<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | Admin</title>

    <!-- Admin CSS files -->
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/Appointments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="shortcut icon" href="favlogo.png" type="logo">

    <style>
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
        <!-- Sidebar (Admin) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-hands-helping"></i>
                    <div>
                        <div style="font-size:1.3rem;">Family Bridge</div>
                        <div>Admin Portal</div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="children-management.php" class="nav-item"><i class="fas fa-child"></i><span>Children Management</span></a>
                <a href="inquiries.php" class="nav-item"><i class="fas fa-question-circle"></i><span>Inquiries</span></a>
                <a href="clients.php" class="nav-item"><i class="fas fa-users"></i><span>Clients</span></a>
                <a href="appointments.php" class="nav-item active"><i class="fas fa-calendar-check"></i><span>Appointments</span></a>
                <a href="documents_review.php" class="nav-item"><i class="fas fa-file-alt"></i><span>Document Review</span></a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                    <div class="page-title">
                        <h1>Appointments</h1>
                        <p>Schedule and manage meetings with clients</p>
                    </div>
                </div>

                <div class="header-right">
                    <div class="admin-profile">
                        <div class="admin-avatar">AD</div>
                        <div class="admin-info">
                            <p>System Administrator</p>
                        </div>
                    </div>

                    <a class="logout-btn" href="../officer_logout.php" style="text-decoration:none;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>

            <div class="content">
                <!-- Stats cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                        <div class="stat-info">
                            <h3 id="totalAppointments">0</h3>
                            <p>Total Appointments</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <h3 id="upcomingAppointments">0</h3>
                            <p>Upcoming</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-info">
                            <h3 id="completedAppointments">0</h3>
                            <p>Completed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                        <div class="stat-info">
                            <h3 id="cancelledAppointments">0</h3>
                            <p>Cancelled</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-controls">
                    <div class="filter-group">
                        <label for="statusFilter">Status:</label>
                        <select id="statusFilter" class="filter-select">
                            <option value="all">All</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <button class="btn btn-secondary" id="refreshAppointmentsBtn" type="button">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>

                <!-- Add Appointment Button -->
                <div class="content-card">
                    <div class="card-header">
                        <h2>Appointments</h2>
                        <button class="btn btn-primary" id="addAppointmentBtn">
                            <i class="fas fa-plus"></i> Add Appointment
                        </button>
                    </div>
                    <div class="card-body">
                        <table class="inquiries-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Confirmed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTableBody">
                                <tr><td colspan="8">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Appointment Modal -->
    <div class="modal" id="appointmentModal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="appointmentModalTitle">Add Appointment</h3>
                <button class="modal-close" id="closeAppointmentModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="appointmentForm">
                    <div class="form-group">
                        <label class="form-label">Client User ID (users.id)</label>
                        <select class="form-control" id="userId" required>
                            <option value="">Select Client</option>
                            <!-- Client options will be populated here -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Appointment Type</label>
                        <input type="text" class="form-control" id="appointmentType" placeholder="home_visit / interview / meeting" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" placeholder="Meeting Title">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="appointmentDate" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Time</label>
                        <input type="time" class="form-control" id="appointmentTime" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" id="duration" placeholder="1 hour" value="1 hour">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Meeting Location</label>
                        <input type="text" class="form-control" id="meetingLocation" placeholder="office / home / online" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="address" rows="2" placeholder="Address..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" rows="3" placeholder="Appointment notes..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-control" id="appointmentStatus">
                            <option value="scheduled">Scheduled</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmed</label>
                        <select class="form-control" id="confirmed">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelAppointmentBtn" type="button">Cancel</button>
                <button class="btn btn-primary" id="saveAppointmentBtn" type="button">Save</button>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "appointments_api.php";

        const tbody = document.getElementById('appointmentsTableBody');
        const refreshBtn = document.getElementById('refreshAppointmentsBtn');
        const addBtn = document.getElementById('addAppointmentBtn');
        const modal = document.getElementById('appointmentModal');
        const closeModalBtn = document.getElementById('closeAppointmentModalBtn');
        const cancelBtn = document.getElementById('cancelAppointmentBtn');
        const saveBtn = document.getElementById('saveAppointmentBtn');
        const modalTitle = document.getElementById('appointmentModalTitle');

        let allAppointments = [];
        let allClients = [];
        let editingId = null;

        async function loadClients() {
            const res = await fetch('clients_api.php?action=list_voted_clients');
            const data = await res.json();
            if (data.success) {
                allClients = data.clients;
                document.getElementById('userId').innerHTML = allClients.map(client =>
                    `<option value="${client.user_id}">${client.name} (${client.email})</option>`
                ).join('');
            }
        }

        async function loadAppointments() {
            const res = await fetch(`${API_URL}?action=list`);
            const data = await res.json();
            if (data.success) {
                allAppointments = data.appointments || [];
                renderAppointments();
            }
        }

        function renderAppointments() {
            tbody.innerHTML = allAppointments.map(a => `
                <tr>
                    <td>${a.client_email}</td>
                    <td>${a.title}</td>
                    <td>${a.appointment_type}</td>
                    <td>${a.appointment_date} ${a.appointment_time}</td>
                    <td>${a.meeting_location}</td>
                    <td>${a.status}</td>
                    <td>${a.confirmed == 1 ? 'Yes' : 'No'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline" data-action="edit" data-id="${a.id}">Edit</button>
                        <button class="btn btn-sm btn-danger" data-action="delete" data-id="${a.id}">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        saveBtn.addEventListener('click', async () => {
            const formData = new FormData(document.getElementById('appointmentForm'));

            const res = await fetch(API_URL, { method: 'POST', body: formData });
            const data = await res.json();
            alert(data.message || (data.success ? 'Appointment Saved' : 'Failed'));

            if (data.success) {
                closeModal();
                loadAppointments();
            }
        });

        document.getElementById('addAppointmentBtn').addEventListener('click', () => {
            editingId = null;
            openModal();
        });

        function openModal() {
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
            editingId = null;
            document.getElementById('appointmentForm').reset();
            modalTitle.textContent = 'Add Appointment';
        }

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        loadClients();
        loadAppointments();
    </script>
</body>

</html>
