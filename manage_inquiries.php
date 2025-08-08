<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle inquiry deletion
if (isset($_POST['delete_inquiry'])) {
    $inquiry_id = $_POST['inquiry_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First delete related booking inquiries
        $stmt = $conn->prepare("DELETE FROM booking_inquiries WHERE id = ?");
        $stmt->bind_param("i", $inquiry_id);
        $stmt->execute();
        
        // Then delete the inquiry
        $stmt = $conn->prepare("DELETE FROM inquiries WHERE id = ?");
        $stmt->bind_param("i", $inquiry_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        $_SESSION['message'] = "✅ Inquiry deleted successfully";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "❌ Error deleting inquiry: " . $e->getMessage();
    }
    
    header("Location: manage_inquiries.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $inquiry_id = $_POST['inquiry_id'];
    $status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $inquiry_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating status";
    }
    header("Location: manage_inquiries.php");
    exit;
}

// Fetch inquiries with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
// Specify the table for status column to avoid ambiguity
$where_clause = $status_filter ? "WHERE i.status = '$status_filter'" : "";

$total_inquiries = $conn->query("SELECT COUNT(*) as count FROM inquiries i $where_clause")->fetch_assoc()['count'];
$total_pages = ceil($total_inquiries / $limit);

// Update the main query to use table alias and specify status column source
$sql = "SELECT i.*, t.title as tour_title 
        FROM inquiries i 
        LEFT JOIN tours t ON i.tour_id = t.id 
        $where_clause 
        ORDER BY i.created_at DESC 
        LIMIT $limit OFFSET $offset";

$inquiries = $conn->query($sql);

// Get counts for status filter
$status_counts = [
    'new' => $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'")->fetch_assoc()['count'],
    'in_progress' => $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'in_progress'")->fetch_assoc()['count'],
    'resolved' => $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'resolved'")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inquiries - Admin Dashboard</title>
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
                <h1>Manage Inquiries</h1>
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

        <!-- Add auto-dismiss script for success and error messages -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-dismiss success messages
                const successMessage = document.querySelector('.alert-success');
                if (successMessage) {
                    setTimeout(function() {
                        successMessage.style.transition = 'opacity 0.5s ease';
                        successMessage.style.opacity = '0';
                        setTimeout(function() {
                            successMessage.style.display = 'none';
                        }, 500);
                    }, 3000);
                }
                
                // Auto-dismiss error messages
                const errorMessage = document.querySelector('.alert-danger');
                if (errorMessage) {
                    setTimeout(function() {
                        errorMessage.style.transition = 'opacity 0.5s ease';
                        errorMessage.style.opacity = '0';
                        setTimeout(function() {
                            errorMessage.style.display = 'none';
                        }, 500);
                    }, 3000);
                }
            });
        </script>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="status-card bg-gradient-primary rounded-4 p-4 h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h4 mb-0 text-white">New Inquiries</h3>
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="fas fa-envelope fa-2x text-white"></i>
                                </div>
                            </div>
                            <p class="display-4 mb-3 text-white fw-bold"><?php echo $status_counts['new']; ?></p>
                            <a href="?status=new" class="btn btn-light btn-sm hover-lift">
                                View Details <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-card bg-gradient-warning rounded-4 p-4 h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h4 mb-0 text-white">In Progress</h3>
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="fas fa-clock fa-2x text-white"></i>
                                </div>
                            </div>
                            <p class="display-4 mb-3 text-white fw-bold"><?php echo $status_counts['in_progress']; ?></p>
                            <a href="?status=in_progress" class="btn btn-light btn-sm hover-lift">
                                View Details <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="status-card bg-gradient-success rounded-4 p-4 h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h4 mb-0 text-white">Resolved</h3>
                                <div class="icon-circle bg-white bg-opacity-25">
                                    <i class="fas fa-check-circle fa-2x text-white"></i>
                                </div>
                            </div>
                            <p class="display-4 mb-3 text-white fw-bold"><?php echo $status_counts['resolved']; ?></p>
                            <a href="?status=resolved" class="btn btn-light btn-sm hover-lift">
                                View Details <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this CSS to your admin.css file or in style tag -->
        <style>
        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df, #224abe);
        }

        .bg-gradient-warning {
            background: linear-gradient(45deg, #f6c23e, #f4b619);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #1cc88a, #169b6b);
        }

        .status-card {
            transition: all 0.3s ease;
            border: none;
        }

        .status-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .icon-circle {
            height: 48px;
            width: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hover-lift {
            transition: all 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
        }

        .table {
            --bs-table-hover-bg: rgba(0, 0, 0, 0.02);
        }

        .table thead th {
            background-color: #f8f9fc;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .btn-group .btn {
            border-radius: 0.35rem;
            margin: 0 0.125rem;
            padding: 0.375rem 0.75rem;
        }

        .form-select {
            border-radius: 0.35rem;
            border-color: #e3e6f0;
        }

        .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .pagination {
            --bs-pagination-color: #4e73df;
            --bs-pagination-hover-color: #224abe;
            --bs-pagination-active-bg: #4e73df;
            --bs-pagination-active-border-color: #4e73df;
        }
        </style>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Customer Inquiries</h3>
                    <div class="btn-group">
                        <a href="?status=" class="btn btn-outline-primary <?php echo !$status_filter ? 'active' : ''; ?>">
                            <i class="fas fa-list-ul me-2"></i>All
                        </a>
                        <a href="?status=new" class="btn btn-outline-primary <?php echo $status_filter === 'new' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope me-2"></i>New
                        </a>
                        <a href="?status=in_progress" class="btn btn-outline-primary <?php echo $status_filter === 'in_progress' ? 'active' : ''; ?>">
                            <i class="fas fa-clock me-2"></i>In Progress
                        </a>
                        <a href="?status=resolved" class="btn btn-outline-primary <?php echo $status_filter === 'resolved' ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle me-2"></i>Resolved
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Tour Package</th>
                                <th class="px-4 py-3">Message</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($inquiry = $inquiries->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4"><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td class="px-4"><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                <td class="px-4"><?php echo htmlspecialchars($inquiry['tour_title'] ?? 'General Inquiry'); ?></td>
                                <td class="px-4">
                                    <div class="inquiry-message text-truncate" style="max-width: 250px;">
                                        <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                                    </div>
                                </td>
                                <td class="px-4">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                        <select name="new_status" class="form-select form-select-sm" 
                                                onchange="this.form.submit()" style="width: 130px;">
                                            <option value="new" <?php echo $inquiry['status'] == 'new' ? 'selected' : ''; ?>>
                                                🔵 New
                                            </option>
                                            <option value="in_progress" <?php echo $inquiry['status'] == 'in_progress' ? 'selected' : ''; ?>>
                                                🟡 In Progress
                                            </option>
                                            <option value="resolved" <?php echo $inquiry['status'] == 'resolved' ? 'selected' : ''; ?>>
                                                🟢 Resolved
                                            </option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td class="px-4"><?php echo date('M d, Y', strtotime($inquiry['created_at'])); ?></td>
                                <td class="px-4">
                                    <div class="btn-group">
                                        <a href="view_inquiry.php?id=<?php echo $inquiry['id']; ?>" 
                                           class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirmDelete('Are you sure you want to delete this inquiry?')">
                                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                            <button type="submit" name="delete_inquiry" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add this CSS to your admin.css file -->
        <style>
        .status-card {
            transition: transform 0.2s;
        }
        .status-card:hover {
            transform: translateY(-5px);
        }
        .inquiry-message {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
        </style>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Inquiry pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>">Previous</a>
                </li>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
<script>
    function confirmDelete(message) {
        return confirm(message);
    }

    // Auto-hide alerts
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        });

        // Add event listeners to delete forms
        document.querySelectorAll('form[onsubmit]').forEach(form => {
            form.onsubmit = function(e) {
                if (!confirm('Are you sure you want to delete this inquiry?')) {
                    e.preventDefault();
                    return false;
                }
                return true;
            };
        });
    });
</script>
</body>
</html>