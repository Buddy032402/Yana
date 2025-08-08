<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Get total counts
$total_tours = $conn->query("SELECT COUNT(*) as count FROM packages")->fetch_assoc()['count'];
$new_inquiries = $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'")->fetch_assoc()['count'];
$pending_reviews = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE status = 'pending'")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Get recent inquiries
$recent_inquiries = $conn->query("
    SELECT name, email, created_at as date FROM inquiries 
    WHERE status = 'new' 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get pending reviews
$pending_reviews_list = $conn->query("
    SELECT u.name as client, t.rating, t.created_at as date 
    FROM testimonials t 
    LEFT JOIN users u ON t.user_id = u.id 
    WHERE t.status = 'pending' 
    ORDER BY t.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Travel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <h1>Dashboard</h1>
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-plane"></i>
                            <h3>Total Tours</h3>
                            <h2><?php echo $total_tours; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-envelope"></i>
                            <h3>New Inquiries</h3>
                            <h2><?php echo $new_inquiries; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-comments"></i>
                            <h3>Pending Reviews</h3>
                            <h2><?php echo $pending_reviews; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-users"></i>
                            <h3>Total Users</h3>
                            <h2><?php echo $total_users; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Inquiries</h3>
                        </div>
                        <div class="card-body">
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
                                    <?php while($inquiry = $recent_inquiries->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                        <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($inquiry['date'])); ?></td>
                                        <td>
                                            <a href="view_inquiry.php?id=<?php echo $inquiry['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Pending Reviews</h3>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Rating</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($review = $pending_reviews_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($review['client']); ?></td>
                                        <td><?php echo $review['rating']; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($review['date'])); ?></td>
                                        <td>
                                            <a href="view_review.php?id=<?php echo $review['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3>Manage Destinations</h3>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDestinationModal">
                                <i class="fas fa-plus"></i> Add Destination
                            </button>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Country</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $destinations = $conn->query("SELECT * FROM destinations ORDER BY name");
                                    while($dest = $destinations->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dest['name']); ?></td>
                                        <td><?php echo htmlspecialchars($dest['country']); ?></td>
                                        <td><?php echo $dest['status'] ? 'Active' : 'Inactive'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-destination" 
                                                    data-id="<?php echo $dest['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($dest['name']); ?>"
                                                    data-country="<?php echo htmlspecialchars($dest['country']); ?>"
                                                    data-status="<?php echo $dest['status']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editDestinationModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Destination Modal -->
    <div class="modal fade" id="editDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editDestinationForm" action="update_destination.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="destination_id" id="edit_destination_id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="edit_destination_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country" id="edit_destination_country" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_destination_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_destination_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Image (optional)</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>