<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle booking status update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Booking status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating booking status";
    }
    header("Location: manage_bookings.php");
    exit;
}

// Handle payment status update
if (isset($_POST['update_payment'])) {
    $booking_id = $_POST['booking_id'];
    $payment_status = $_POST['payment_status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $payment_status, $booking_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Payment status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating payment status";
    }
    header("Location: manage_bookings.php");
    exit;
}

// Add this after the other status update handlers
if (isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Booking deleted successfully";
    } else {
        $_SESSION['error'] = "❌ Error deleting booking";
    }
    header("Location: manage_bookings.php");
    exit;
}

// Fetch bookings with pagination and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where_clauses = [];
$params = [];
$param_types = "";

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clauses[] = "b.status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

if (isset($_GET['payment_status']) && $_GET['payment_status'] !== '') {
    $where_clauses[] = "b.payment_status = ?";
    $params[] = $_GET['payment_status'];
    $param_types .= "s";
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = "%" . $_GET['search'] . "%";
    $where_clauses[] = "(u.username LIKE ? OR t.title LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $param_types .= "ss";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total bookings count
$count_sql = "SELECT COUNT(*) as count FROM bookings b 
              LEFT JOIN users u ON b.user_id = u.id 
              LEFT JOIN packages p ON b.package_id = p.id 
              $where_sql";

if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $total_count = $count_stmt->get_result()->fetch_assoc()['count'];
} else {
    $total_count = $conn->query($count_sql)->fetch_assoc()['count'];
}

$total_pages = ceil($total_count / $limit);

// Get bookings for current page
// Update the bookings list query
$sql = "SELECT b.*, u.name as username, u.email, u.phone,
        p.name as tour_title, p.price as tour_price,
        b.number_of_travelers, b.booking_date, b.special_requests,
        b.total_amount, b.status, b.payment_status,
        b.created_at, b.updated_at
        FROM bookings b 
        LEFT JOIN users u ON b.user_id = u.id 
        LEFT JOIN packages p ON b.package_id = p.id 
        $where_sql 
        ORDER BY b.created_at DESC  
        LIMIT ? OFFSET ?";

// Update statistics queries
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'],
    'confirmed' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['count'],
    'cancelled' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'")->fetch_assoc()['count'],
    'unpaid' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'unpaid'")->fetch_assoc()['count'],
    'paid' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'paid'")->fetch_assoc()['count']
];
$stmt = $conn->prepare($sql);
$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result();

// Get booking statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch_assoc()['count'],
    'confirmed' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['count'],
    'cancelled' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'")->fetch_assoc()['count'],
    'unpaid' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'unpaid'")->fetch_assoc()['count'],
    'paid' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE payment_status = 'paid'")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Manage Bookings</h1>
            </div>
        </header>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body p-0">
                <div class="stats-grid">
                    <div class="stats-card bg-primary text-white">
                        <h4>Total Bookings</h4>
                        <p class="h2"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="stats-card bg-warning text-white">
                        <h4>Pending</h4>
                        <p class="h2"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="stats-card bg-success text-white">
                        <h4>Confirmed</h4>
                        <p class="h2"><?php echo $stats['confirmed']; ?></p>
                    </div>
                    <div class="stats-card bg-danger text-white">
                        <h4>Cancelled</h4>
                        <p class="h2"><?php echo $stats['cancelled']; ?></p>
                    </div>
                    <div class="stats-card bg-secondary text-white">
                        <h4>Unpaid</h4>
                        <p class="h2"><?php echo $stats['unpaid']; ?></p>
                    </div>
                    <div class="stats-card bg-info text-white">
                        <h4>Paid</h4>
                        <p class="h2"><?php echo $stats['paid']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="mb-0">Booking List</h3>
                    <div class="booking-filters mt-2 mt-md-0 w-100 w-md-auto">
                        <form class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search bookings..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            
                            <select name="status" class="form-select status-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            
                            <select name="payment_status" class="form-select status-select">
                                <option value="">All Payments</option>
                                <option value="unpaid" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                <option value="paid" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="refunded" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover booking-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Package</th>
                                <th>Date</th>
                                <th>Persons</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($booking['tour_title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td class="text-center"><?php echo $booking['number_of_travelers']; ?></td>
                                <td class="fw-bold">₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="new_status" class="form-select form-select-sm status-badge <?php echo $booking['status']; ?>" 
                                                onchange="this.form.submit()">
                                            <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>✅ Confirmed</option>
                                            <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="payment_status" class="form-select form-select-sm payment-badge <?php echo $booking['payment_status']; ?>" 
                                                onchange="this.form.submit()">
                                            <option value="unpaid" <?php echo $booking['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>💰 Unpaid</option>
                                            <option value="paid" <?php echo $booking['payment_status'] == 'paid' ? 'selected' : ''; ?>>✅ Paid</option>
                                            <option value="refunded" <?php echo $booking['payment_status'] == 'refunded' ? 'selected' : ''; ?>>↩️ Refunded</option>
                                        </select>
                                        <input type="hidden" name="update_payment" value="1">
                                    </form>
                                </td>
                                <td>
                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" name="delete_booking" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Booking pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&payment_status=<?php echo $_GET['payment_status'] ?? ''; ?>&search=<?php echo $_GET['search'] ?? ''; ?>">Previous</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&payment_status=<?php echo $_GET['payment_status'] ?? ''; ?>&search=<?php echo $_GET['search'] ?? ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $_GET['status'] ?? ''; ?>&payment_status=<?php echo $_GET['payment_status'] ?? ''; ?>&search=<?php echo $_GET['search'] ?? ''; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stats-card {
    padding: 1.25rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-card h4 {
    font-size: 1rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stats-card .h2 {
    font-size: 2rem;
    margin: 0;
    font-weight: bold;
}

.booking-filters {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.booking-table th {
    background: #f8f9fa;
    white-space: nowrap;
}

.booking-table td {
    vertical-align: middle;
}

.status-select {
    min-width: 130px;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .booking-filters form {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .booking-filters .form-control,
    .booking-filters .form-select,
    .booking-filters .btn {
        width: 100%;
    }
}

.status-badge,
.payment-badge {
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.875rem;
    width: auto;
    min-width: 120px;
    cursor: pointer;
}

.status-badge.pending {
    background-color: #ffeeba;
    color: #856404;
}

.status-badge.confirmed {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

.payment-badge.unpaid {
    background-color: #e2e3e5;
    color: #383d41;
}

.payment-badge.paid {
    background-color: #d4edda;
    color: #155724;
}

.payment-badge.refunded {
    background-color: #cce5ff;
    color: #004085;
}

.booking-table td {
    padding: 1rem 0.75rem;
}

.booking-table .btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.booking-table .btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
<script>
    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    });
</script>
</body>
</html>