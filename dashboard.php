<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Error handling function
function handleQueryError($conn, $query) {
    if (!$query) {
        error_log("Query failed: " . $conn->error);
        return false;
    }
    return $query;
}

// Get statistics with error handling
// Update the stats array in the try-catch block
try {
    $stats = [
        'destinations' => handleQueryError($conn, $conn->query("SELECT COUNT(*) as count FROM destinations"))->fetch_assoc()['count'],
        'packages' => handleQueryError($conn, $conn->query("SELECT COUNT(*) as count FROM packages"))->fetch_assoc()['count'],
        'users' => handleQueryError($conn, $conn->query("SELECT COUNT(*) as count FROM users"))->fetch_assoc()['count'],
        'inquiries' => handleQueryError($conn, $conn->query("SELECT COUNT(*) as count FROM inquiries"))->fetch_assoc()['count'],
        'bookings' => handleQueryError($conn, $conn->query("SELECT COUNT(*) as count FROM bookings"))->fetch_assoc()['count']
    ];
} catch (Exception $e) {
    error_log("Error fetching statistics: " . $e->getMessage());
    $stats = ['destinations' => 0, 'inquiries' => 0, 'packages' => 0, 'users' => 0, 'bookings' => 0];
}

// Get recent inquiries
// Update the recent inquiries query to only show booking inquiries
$recent_inquiries = $conn->query("
    SELECT * FROM inquiries 
    WHERE type = 'booking' 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get recent destinations with error handling
try {
    $recent_destinations = handleQueryError($conn, $conn->query("
        SELECT * FROM destinations 
        ORDER BY created_at DESC 
        LIMIT 5
    "));
} catch (Exception $e) {
    error_log("Error fetching recent destinations: " . $e->getMessage());
    $recent_destinations = null;
}

// Update the messages query to include contact form messages
$recent_messages_query = "
    SELECT 
        id,
        sender_name,
        sender_email as email,
        subject,
        message,
        created_at,
        is_read
    FROM admin_messages 
    ORDER BY created_at DESC 
    LIMIT 5";
$recent_messages = $conn->query($recent_messages_query);

// Add a query for recent bookings
// Fix the recent bookings query
$recent_bookings_query = "
    SELECT b.*, p.name as package_name, u.name as username
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 5
";
$recent_bookings = $conn->query($recent_bookings_query);

// Update the testimonials query
$testimonials_query = "
    SELECT t.*, p.name as package_name, t.customer_name as client_name
    FROM testimonials t
    LEFT JOIN packages p ON t.package_id = p.id
    WHERE t.status = 'pending'
    ORDER BY t.created_at DESC 
    LIMIT 5
";
$recent_testimonials = $conn->query($testimonials_query);

?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>
    
    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Overview</h1>
                <!-- Removing the theme toggle button -->
            </div>
            <!-- Remove the dashboard-actions div entirely -->
        </header>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Total Destinations</h3>
                <h1 class="stat-number"><?php echo $stats['destinations']; ?></h1>
            </div>
            <div class="stat-card">
                <i class="fas fa-suitcase"></i>
                <h3>Total Packages</h3>
                <h1 class="stat-number"><?php echo $stats['packages']; ?></h1>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3>Total Users</h3>
                <h1 class="stat-number"><?php echo $stats['users']; ?></h1>
            </div>
            <div class="stat-card">
                <i class="fas fa-envelope"></i>
                <h3>Total Inquiries</h3>
                <h1 class="stat-number"><?php echo $stats['inquiries']; ?></h1>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Total Bookings</h3>
                <h1 class="stat-number"><?php echo $stats['bookings']; ?></h1>
            </div>
        </div>

        <div class="dashboard-content">
           

            <!-- Main content rows -->
            <div class="row">
                <!-- Add Recent Bookings Card here, before other cards -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="m-0">Recent Bookings</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Package</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                
                                        <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $booking['status'] == 'confirmed' ? 'success' : ($booking['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Inquiries Card and other cards follow -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="m-0">Recent Inquiries</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $recent_inquiries->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="view_inquiry.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changed from Reviews to Recent Destinations -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Destinations</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Country</th>
                                            <th>Date Added</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $recent_destinations = $conn->query("
                                            SELECT * FROM destinations 
                                            ORDER BY created_at DESC 
                                            LIMIT 5
                                        ");
                                        while($row = $recent_destinations->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['country']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="manage_destinations.php?highlight=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Recent Messages Card -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="m-0">Recent Messages</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="recentMessagesTable">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($recent_messages && $recent_messages->num_rows > 0): ?>
                                            <?php while($message = $recent_messages->fetch_assoc()): ?>
                                            <tr class="<?php echo !$message['is_read'] ? 'table-warning' : ''; ?>">
                                                <td><?php echo htmlspecialchars($message['sender_name']); ?></td>
                                                <td class="email-cell">
                                                    <div class="email-preview">
                                                        <?php echo htmlspecialchars($message['email']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="message-preview">
                                                        <a href="inbox.php?view=<?php echo $message['id']; ?>" class="message-link">
                                                            <?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M d, h:i A', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $message['is_read'] ? 'success' : 'warning'; ?>">
                                                        <?php echo $message['is_read'] ? 'Read' : 'New'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No messages found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Recent User Logins Card -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent User Logins</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table" id="recentLoginsTable">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Login Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_logins = $conn->query("
                                            SELECT l.*, u.name as username 
                                            FROM login_history l
                                            JOIN users u ON l.user_id = u.id
                                            ORDER BY login_time DESC 
                                            LIMIT 5
                                        ");
                                        while($login = $recent_logins->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($login['username']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($login['login_time'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $login['status'] == 'success' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($login['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>

    
    <script>
        // Make message rows clickable
        document.addEventListener('DOMContentLoaded', function() {
            const messageTable = document.getElementById('recentMessagesTable');
            if (messageTable) {
                messageTable.addEventListener('click', function(e) {
                    const row = e.target.closest('tr');
                    if (row) {
                        const messageId = row.getAttribute('data-message-id');
                        if (messageId) {
                            window.location.href = 'inbox.php?view=' + messageId;
                        } else {
                            const link = row.querySelector('.message-link');
                            if (link) {
                                window.location.href = link.getAttribute('href');
                            }
                        }
                    }
                });
            }
        });
    </script>

    <script>
        // Modify the updateRecentMessages function to preserve visibility
        function updateRecentMessages() {
            fetch('ajax/get_recent_messages.php')
                .then(response => response.text())
                .then(data => {
                    if(data.trim() !== '') {
                        document.querySelector('#recentMessagesTable tbody').innerHTML = data;
                    }
                })
                .catch(error => {
                    console.error('Error updating messages:', error);
                });
        }

        function updateRecentLogins() {
            fetch('ajax/get_recent_logins.php')
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#recentLoginsTable tbody').innerHTML = data;
                });
        }

        // Update both tables every 10 seconds
        setInterval(() => {
            updateRecentMessages();
            updateRecentLogins();
        }, 10000);

        // Initial load
        updateRecentMessages();
        updateRecentLogins();

        // Remove the exportData function and its references

        // Search functionality
        function searchTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tr');

            input.addEventListener('keyup', function() {
                const filter = input.value.toLowerCase();
                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    const cells = row.getElementsByTagName('td');
                    let found = false;
                    
                    for (let cell of cells) {
                        if (cell.textContent.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                    row.style.display = found ? '' : 'none';
                }
            });
        }

        // Initialize search for all tables
        document.addEventListener('DOMContentLoaded', function() {
            searchTable('inquiriesSearch', 'inquiriesTable');
            searchTable('reviewsSearch', 'reviewsTable');
            searchTable('messagesSearch', 'recentMessagesTable');
            searchTable('loginsSearch', 'recentLoginsTable');
        });

        // Real-time notifications
        let notificationSocket;
        
        function connectWebSocket() {
            notificationSocket = new WebSocket('ws://localhost:8080');
            
            notificationSocket.onmessage = function(event) {
                const notification = JSON.parse(event.data);
                showNotification(notification.message);
            };

            notificationSocket.onclose = function() {
                setTimeout(connectWebSocket, 5000);
            };
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'toast show';
            notification.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">Notification</strong>
                    <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
                <div class="toast-body">${message}</div>
            `;
            document.getElementById('notificationContainer').appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }

        // Initialize WebSocket connection
        connectWebSocket();
    </script>

    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            width: 100%;
            /* Reduced margin for better spacing */
        }
        
        .dashboard-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table-responsive {
            margin: 0;
        }
        
        /* Adjusted responsive sidebar handling with minimal spacing */
        @media (min-width: 993px) {
            .sidebar {
                width: 250px;
                position: fixed;
                height: 100%;
            }
            
            .main-content {
                margin-left: 5px; /* Reduced to 5px as requested */
                padding-left: 250px; /* Add padding to account for sidebar width */
            }
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding-left: 0;
            }
            
            .sidebar {
                display: none;
            }
            
            .sidebar.active {
                display: block;
                position: fixed;
                width: 250px;
                height: 100%;
                z-index: 1000;
            }
        }
    </style>
    
    <!-- Add notification container div -->
    <div id="notificationContainer"></div>
</body>
</html>

<style>
        /* Message link styling */
        .message-link {
            color: inherit;
            text-decoration: none;
            display: block;
            width: 100%;
        }
        
        .message-link:hover {
            color: #0d6efd;
            text-decoration: underline;
        }
        
        /* Email cell styling */
        .email-cell {
            max-width: 180px;
        }
        
        .email-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        
        /* Improve table responsiveness */
        .table th, .table td {
            padding: 0.5rem;
            vertical-align: middle;
        }
        
        /* Make sure date column has consistent width */
        .table th:nth-child(4), .table td:nth-child(4) {
            min-width: 100px;
            width: 100px;
        }
        
        /* Make entire row clickable */
        #recentMessagesTable tbody tr {
            cursor: pointer;
        }
    </style>