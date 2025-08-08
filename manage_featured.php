<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle featured status toggle
if (isset($_POST['toggle_featured'])) {
    $destination_id = $_POST['destination_id'];
    $featured = $_POST['featured'] ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE destinations SET featured = ? WHERE id = ?");
    $stmt->bind_param("ii", $featured, $destination_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Featured status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating featured status";
    }
    header("Location: manage_featured.php");
    exit;
}

// Fetch all featured destinations
// Update the query to join with featured_destinations table
$featured_destinations = $conn->query("
    SELECT d.*, fd.featured_order, fd.highlight_text 
    FROM destinations d 
    LEFT JOIN featured_destinations fd ON d.id = fd.destination_id 
    WHERE d.featured = 1 
    ORDER BY fd.featured_order ASC, d.name ASC
");

?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Featured Destinations - Admin Dashboard</title>
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
                <h1>Featured Destinations</h1>
                <a href="add_featured.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Featured
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
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Highlight</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($destination = $featured_destinations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $destination['featured_order'] ?? 'N/A'; ?></td>
                                <td>
                                    <img src="../uploads/destinations/<?php echo $destination['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($destination['name']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                </td>
                                <td><?php echo htmlspecialchars($destination['name']); ?></td>
                                <td><?php echo htmlspecialchars($destination['country']); ?></td>
                                <td><?php echo htmlspecialchars($destination['highlight_text'] ?? ''); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="destination_id" value="<?php echo $destination['id']; ?>">
                                        <input type="hidden" name="featured" value="<?php echo $destination['featured']; ?>">
                                        <button type="submit" name="toggle_featured" class="btn btn-sm <?php echo $destination['featured'] ? 'btn-success' : 'btn-secondary'; ?>">
                                            <?php echo $destination['featured'] ? 'Featured' : 'Not Featured'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="view_featured.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_featured.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete(<?php echo $destination['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
</body>
</html>

// Add this JavaScript at the bottom of the file
<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to remove this destination from featured?')) {
        window.location.href = `remove_featured.php?id=${id}`;
    }
}
</script>