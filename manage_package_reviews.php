<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

// Fetch package details
$stmt = $conn->prepare("SELECT name FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

if (!$package) {
    header("Location: manage_packages.php");
    exit;
}

// Handle review status toggle
if (isset($_POST['toggle_status'])) {
    $review_id = $_POST['review_id'];
    $status = $_POST['status'] ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE package_reviews SET status = ? WHERE id = ? AND package_id = ?");
    $stmt->bind_param("iii", $status, $review_id, $package_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Review status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating review status";
    }
    header("Location: manage_package_reviews.php?package_id=" . $package_id);
    exit;
}

// Handle review deletion
if (isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    
    $stmt = $conn->prepare("DELETE FROM package_reviews WHERE id = ? AND package_id = ?");
    $stmt->bind_param("ii", $review_id, $package_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Review deleted successfully";
    } else {
        $_SESSION['error'] = "❌ Error deleting review";
    }
    header("Location: manage_package_reviews.php?package_id=" . $package_id);
    exit;
}

// Handle admin reply
if (isset($_POST['add_reply'])) {
    $review_id = $_POST['review_id'];
    $reply = $_POST['reply'];
    $reply_date = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("UPDATE package_reviews SET admin_reply = ?, reply_date = ? WHERE id = ? AND package_id = ?");
    $stmt->bind_param("ssii", $reply, $reply_date, $review_id, $package_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Reply added successfully";
    } else {
        $_SESSION['error'] = "❌ Error adding reply";
    }
    header("Location: manage_package_reviews.php?package_id=" . $package_id);
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where_clauses = ["r.package_id = ?"];
$params = [$package_id];
$param_types = "i";

if (isset($_GET['rating']) && $_GET['rating'] !== '') {
    $where_clauses[] = "r.rating = ?";
    $params[] = $_GET['rating'];
    $param_types .= "i";
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clauses[] = "r.status = ?";
    $params[] = $_GET['status'];
    $param_types .= "i";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get total reviews count
$count_sql = "SELECT COUNT(*) as count FROM package_reviews r $where_sql";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_reviews / $limit);

// Get reviews for current page
$sql = "SELECT r.*, u.name as user_name, u.email as user_email 
        FROM package_reviews r 
        LEFT JOIN users u ON r.user_id = u.id 
        $where_sql 
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?";
$param_types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$reviews = $stmt->get_result();

// Get rating statistics
$stats_sql = "SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
              FROM package_reviews 
              WHERE package_id = ? AND status = 1";
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
    <title>Package Reviews - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .rating-bar {
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .rating-fill {
            height: 100%;
            background-color: #ffc107;
        }
        .review-card {
            border-left: 4px solid #dee2e6;
            margin-bottom: 1rem;
            padding: 1rem;
        }
        .review-card.featured {
            border-left-color: #28a745;
        }
        .review-card.pending {
            border-left-color: #ffc107;
        }
        .admin-reply {
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
                <h1>Reviews: <?php echo htmlspecialchars($package['name']); ?></h1>
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
                        <h5 class="mb-0">Rating Statistics</h5>
                    </div>
                    <div class="card-body">
                        <h2 class="text-center mb-4">
                            <?php echo number_format($stats['avg_rating'], 1); ?>
                            <small class="text-muted">/ 5</small>
                        </h2>
                        
                        <?php
                        $ratings = [
                            5 => $stats['five_star'],
                            4 => $stats['four_star'],
                            3 => $stats['three_star'],
                            2 => $stats['two_star'],
                            1 => $stats['one_star']
                        ];
                        
                        foreach ($ratings as $stars => $count):
                            $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                        ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo $stars; ?> stars</span>
                                <span><?php echo $count; ?> reviews</span>
                            </div>
                            <div class="rating-bar">
                                <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <p class="text-center mt-3">
                            Total Reviews: <?php echo $stats['total_reviews']; ?>
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Filter Reviews</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select">
                                    <option value="">All Ratings</option>
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo isset($_GET['rating']) && $_GET['rating'] == $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> Stars
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="1" <?php echo isset($_GET['status']) && $_GET['status'] === '1' ? 'selected' : ''; ?>>Published</option>
                                    <option value="0" <?php echo isset($_GET['status']) && $_GET['status'] === '0' ? 'selected' : ''; ?>>Pending</option>
                                </select>
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
                        <h5 class="mb-0">Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php while($review = $reviews->fetch_assoc()): ?>
                            <div class="review-card <?php echo $review['status'] ? 'featured' : 'pending'; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                        <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                    <div>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                
                                <?php if ($review['admin_reply']): ?>
                                    <div class="admin-reply">
                                        <small class="text-muted">Admin Reply - <?php echo date('F j, Y', strtotime($review['reply_date'])); ?></small>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="showReplyModal(<?php echo $review['id']; ?>, <?php echo json_encode($review['admin_reply']); ?>)">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                    
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $review['status']; ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $review['status'] ? 'success' : 'warning'; ?>">
                                            <?php echo $review['status'] ? 'Published' : 'Pending'; ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" name="delete_review" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>

                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Review pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?package_id=<?php echo $package_id; ?>&page=<?php echo $page-1; ?>&rating=<?php echo $_GET['rating'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Previous</a>
                                </li>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?package_id=<?php echo $package_id; ?>&page=<?php echo $i; ?>&rating=<?php echo $_GET['rating'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?package_id=<?php echo $package_id; ?>&page=<?php echo $page+1; ?>&rating=<?php echo $_GET['rating'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>