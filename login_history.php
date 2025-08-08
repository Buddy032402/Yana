<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Remove the entire archiveOldRecords function and its call
// Remove archive directory creation code

// Add this function at the top of the file after include "../db.php";
// Update the getBrowserInfo function
function getBrowserInfo($userAgent) {
    if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
        return '<i class="fab fa-chrome fa-lg" style="color: #4285F4;"></i>';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        return '<i class="fab fa-firefox fa-lg" style="color: #FF9400;"></i>';
    } elseif (strpos($userAgent, 'Edg') !== false) {
        return '<i class="fab fa-edge fa-lg" style="color: #0078D7;"></i>';
    } else {
        return '<i class="fas fa-globe fa-lg" style="color: #4285F4;"></i>';
    }
}

// Get login history with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Update query to show both current and archived records
// Modify the query to remove type-related columns
// Update query to only show records within last 24 hours
// Update query to show only last 100 records
$query = "SELECT 
            lh.id,
            lh.username,
            u.email as user_email,
            lh.login_time,
            lh.user_agent,
            lh.status,
            DATE(lh.login_time) as login_date
          FROM login_history lh 
          LEFT JOIN users u ON lh.username = u.name 
          ORDER BY login_time DESC LIMIT 100";

// Remove pagination variables since we're showing fixed 100 records
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

// Remove total records count and pagination logic
$total_query = "SELECT 
    (SELECT COUNT(*) FROM login_history) +
    (SELECT COUNT(*) FROM login_history_archive) as count";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get total count of login records
$total_count = $conn->query("SELECT COUNT(*) as total FROM login_history")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login History - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        
        .table td .d-flex {
            justify-content: center;
        }
        
        .badge {
            min-width: 80px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Login History</h1>
                <div class="alert alert-info">
                    Total Login Records: <strong><?php echo number_format($total_count); ?></strong>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Login Time</th>
                                    <th>Browser Info</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_email'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['login_time'])); ?></td>
                                    <td><div class="d-flex align-items-center gap-2">
                                        <?php echo getBrowserInfo($row['user_agent']); ?>
                                    </div></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] == 'success' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php /* Remove pagination section */ ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Remove the confirmArchive script -->
</body>
</html>