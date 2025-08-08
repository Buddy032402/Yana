<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

// Fetch package details
$stmt = $conn->prepare("SELECT name, price FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
    header("Location: manage_packages.php");
    exit;
}

// Handle booking status update
if (isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, admin_notes = ? WHERE id = ? AND package_id = ?");
    $stmt->bind_param("ssii", $status, $admin_notes, $booking_id, $package_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Booking status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating booking status";
    }
    header("Location: manage_package_bookings.php?package_id=" . $package_id);
    exit;
}

// Pagination and filtering setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = ["b.package_id = ?"];
$params = [$package_id];
$param_types = "i";

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clauses[] = "b.status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
    $where_clauses[] = "b.travel_date >= ?";
    $params[] = $_GET['date_from'];
    $param_types .= "s";
}

if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
    $where_clauses[] = "b.travel_date <= ?";
    $params[] = $_GET['date_to'];
    $param_types .= "s";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get total bookings count
$count_sql = "SELECT COUNT(*) as count FROM bookings b $where_sql";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$total_bookings = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_bookings / $limit);

// Get bookings for current page
$sql = "SELECT b.*, u.name as user_name, u.email as user_email,
               pt.name as pricing_tier_name, pt.price as tier_price
        FROM bookings b 
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN package_pricing_tiers pt ON b.pricing_tier_id = pt.id
        $where_sql 
        ORDER BY b.created_at DESC 
        LIMIT ? OFFSET ?";
$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result();

// Get booking statistics
$stats_sql = "SELECT 
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                SUM(total_amount) as total_revenue
              FROM bookings 
              WHERE package_id = ?";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("i", $package_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Bookings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .booking-card {
            border-left: 4px solid #dee2e6;
            margin-bottom: 1rem;
            padding: 1rem;
        }
        .booking-card.confirmed { border-left-color: #28a745; }
        .booking-card.pending { border-left-color: #ffc107; }
        .booking-card.cancelled { border-left-color: #dc3545; }
        .status-badge {
            text-transform: capitalize;
            font-weight: 500;
        }
        .admin-notes {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            margin-top: 1rem;
            padding: 1rem;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>Bookings: <?php echo htmlspecialchars($package['name']); ?></h1>
                <a href="manage_packages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Packages
                </a>
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

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Booking Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <h3><?php echo $stats['total_bookings']; ?></h3>
                                <small class="text-muted">Total Bookings</small>
                            </div>
                            <div class="col-6 mb-3">
                                <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                                <small class="text-muted">Total Revenue</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-success"><?php echo $stats['confirmed']; ?></h4>
                                <small class="text-muted">Confirmed</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-warning"><?php echo $stats['pending']; ?></h4>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <h4 class="text-danger"><?php echo $stats['cancelled']; ?></h4>
                                <small class="text-muted">Cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Bookings</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Travel Date From</label>
                                <input type="date" name="date_from" class="form-control" 
                                       value="<?php echo $_GET['date_from'] ?? ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Travel Date To</label>
                                <input type="date" name="date_to" class="form-control" 
                                       value="<?php echo $_GET['date_to'] ?? ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php while($booking = $bookings->fetch_assoc()): ?>
                            <div class="booking-card <?php echo $booking['status']; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($booking['user_name']); ?>
                                            <small class="text-muted">(<?php echo htmlspecialchars($booking['user_email']); ?>)</small>
                                        </h6>
                                        <small class="text-muted">Booked on <?php echo date('F j, Y', strtotime($booking['created_at'])); ?></small>
                                    </div>
                                    <span class="badge bg-<?php 
                                        echo $booking['status'] === 'confirmed' ? 'success' : 
                                            ($booking['status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?> status-badge">
                                        <?php echo $booking['status']; ?>
                                    </span>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>Travel Date:</strong> 
                                            <?php echo date('F j, Y', strtotime($booking['travel_date'])); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Persons:</strong> 
                                            <?php echo $booking['num_persons']; ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Pricing Tier:</strong> 
                                            <?php echo htmlspecialchars($booking['pricing_tier_name']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>Total Amount:</strong> 
                                            $<?php echo number_format($booking['total_amount'], 2); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Payment Status:</strong> 
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </p>
                                        <?php if ($booking['payment_date']): ?>
                                            <p class="mb-1">
                                                <strong>Paid on:</strong> 
                                                <?php echo date('F j, Y', strtotime($booking['payment_date'])); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($booking['special_requests']): ?>
                                    <div class="mt-2">
                                        <strong>Special Requests:</strong>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($booking['admin_notes']): ?>
                                    <div class="admin-notes">
                                        <strong>Admin Notes:</strong>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['admin_notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="showUpdateModal(<?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>', <?php echo json_encode($booking['admin_notes']); ?>)">
                                        <i class="fas fa-edit"></i> Update Status
                                    </button>
                                    
                                    <a href="view_booking_details.php?id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Booking pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?package_id=<?php echo $package_id; ?>&page=<?php echo $page-1; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from='.$_GET['date_from'] : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to='.$_GET['date_to'] : ''; ?>">Previous</a>
                                    </li>
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?package_id=<?php echo $package_id; ?>&page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from='.$_GET['date_from'] : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to='.$_GET['date_to'] : ''; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?package_id=<?php echo $package_id; ?>&page=<?php echo $page+1; ?><?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from='.$_GET['date_from'] : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to='.$_GET['date_to'] : ''; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="booking_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="booking_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" id="admin_notes" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showUpdateModal(bookingId, status, notes) {
        document.getElementById('booking_id').value = bookingId;
        document.getElementById('booking_status').value = status;
        document.getElementById('admin_notes').value = notes || '';
        
        const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
        modal.show();
    }
</script>
</body>
</html>