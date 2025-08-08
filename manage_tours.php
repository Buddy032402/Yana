<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle tour status toggle
if (isset($_POST['toggle_status'])) {
    $tour_id = $_POST['tour_id'];
    $status = $_POST['status'] ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE tours SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $tour_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Tour status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating tour status";
    }
    header("Location: manage_tours.php");
    exit;
}

// Handle tour deletion
if (isset($_POST['delete_tour'])) {
    $tour_id = $_POST['tour_id'];
    
    // Check if tour has any bookings - Updated column name from tour_id to package_id
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE package_id = ?");
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $booking_count = $stmt->get_result()->fetch_assoc()['count'];
    
    if ($booking_count > 0) {
        $_SESSION['error'] = "❌ Cannot delete tour with existing bookings";
    } else {
        $stmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
        $stmt->bind_param("i", $tour_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Tour deleted successfully";
        } else {
            $_SESSION['error'] = "❌ Error deleting tour";
        }
    }
    header("Location: manage_tours.php");
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = [];
$params = [];
$param_types = "";

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $where_clauses[] = "(title LIKE ? OR location LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
    $param_types .= "ss";
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clauses[] = "status = ?";
    $params[] = $_GET['status'];
    $param_types .= "i";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total tours count
$count_sql = "SELECT COUNT(*) as count FROM tours $where_sql";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_tours = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_tours / $limit);

// Get tours for current page
$sql = "SELECT * FROM tours $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$tours = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tours - Admin Dashboard</title>
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
                <h1>Manage Tours</h1>
                <a href="add_tour.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Tour
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

        <div class="card">
            <div class="card-header">
                <form class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search tours..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" <?php echo isset($_GET['status']) && $_GET['status'] === '1' ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo isset($_GET['status']) && $_GET['status'] === '0' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($tour = $tours->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?php echo $tour['image_main']; ?>" 
                                         alt="<?php echo htmlspecialchars($tour['title']); ?>" 
                                         class="img-thumbnail" style="width: 100px;">
                                </td>
                                <td><?php echo htmlspecialchars($tour['title']); ?></td>
                                <td><?php echo htmlspecialchars($tour['location']); ?></td>
                                <td>₱<?php echo number_format($tour['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($tour['duration']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="tour_id" value="<?php echo $tour['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $tour['status']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $tour['status'] ? 'success' : 'danger'; ?>">
                                            <?php echo $tour['status'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($tour['featured']): ?>
                                        <span class="badge bg-primary">Featured</span>
                                    <?php endif; ?>
                                    <?php if ($tour['popular']): ?>
                                        <span class="badge bg-warning">Popular</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_tour.php?id=<?php echo $tour['id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this tour?');">
                                        <input type="hidden" name="tour_id" value="<?php echo $tour['id']; ?>">
                                        <button type="submit" name="delete_tour" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Tour pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Previous</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
</body>
</html>