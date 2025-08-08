<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_top_pick'])) {
        $destination_id = $_POST['destination_id'];
        $package_id = $_POST['package_id'] ?? null;  // Add this line
        $featured_order = $_POST['featured_order'];
        $description = $_POST['description'];
        $client_name = $_POST['client_name'];
        $client_rating = $_POST['client_rating'];
        
        // Handle client image upload
        $client_image = "default-user.png"; // Default image
        
        if (isset($_FILES['client_image']) && $_FILES['client_image']['error'] == 0) {
            $upload_dir = "../uploads/clients/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['client_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('client_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['client_image']['tmp_name'], $upload_path)) {
                $client_image = $new_filename;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO top_picks (destination_id, package_id, featured_order, description, client_name, client_image, client_rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssi", $destination_id, $package_id, $featured_order, $description, $client_name, $client_image, $client_rating);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Top pick added successfully";
        } else {
            $_SESSION['error'] = "❌ Error adding top pick";
        }
    }
    
    if (isset($_POST['update_top_pick'])) {
        $pick_id = $_POST['pick_id'];
        $featured_order = $_POST['featured_order'];
        $description = $_POST['description'];
        $client_name = $_POST['client_name'];
        $client_rating = $_POST['client_rating'];
        
        $stmt = $conn->prepare("UPDATE top_picks SET featured_order = ?, description = ?, client_name = ?, client_rating = ? WHERE id = ?");
        $stmt->bind_param("issii", $featured_order, $description, $client_name, $client_rating, $pick_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Top pick updated successfully";
        } else {
            $_SESSION['error'] = "❌ Error updating top pick";
        }
    }
    
    if (isset($_POST['delete_top_pick'])) {
        $pick_id = $_POST['pick_id'];
        
        $stmt = $conn->prepare("DELETE FROM top_picks WHERE id = ?");
        $stmt->bind_param("i", $pick_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Top pick deleted successfully";
        } else {
            $_SESSION['error'] = "❌ Error deleting top pick: " . $conn->error;
        }
    }
    
    header("Location: manage_top_picks.php");
    exit;
}


// Update the query to only show selected packages
$top_picks = $conn->query("
    SELECT tp.*, 
           d.name as destination_name, 
           d.image as destination_image,
           p.name as package_name, 
           p.price, 
           p.duration,
           p.image as package_image
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    LEFT JOIN packages p ON tp.package_id = p.id
    ORDER BY tp.featured_order, tp.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Top Client Picks - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .top-pick-card {
            transition: all 0.3s ease;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
        }
        .top-pick-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .top-pick-img {
            height: 200px;
            object-fit: cover;
        }
        .client-info {
            display: flex;
            align-items: center;
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .client-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3949ab;
            margin-right: 10px;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1rem;
        }
        .order-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #3949ab;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .action-buttons {
            position: absolute;
            bottom: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Manage Top Client Picks</h1>
                    <a href="add_top_picks.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Top Pick
                    </a>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php if ($top_picks->num_rows === 0): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                No top picks found. Add your first top pick!
                            </div>
                        </div>
                    <?php else: ?>
                        <?php while($pick = $top_picks->fetch_assoc()): ?>
                            <div class="col">
                                <div class="card top-pick-card position-relative">
                                    <span class="order-badge">
                                        <i class="fas fa-sort-numeric-down"></i> Order: <?php echo $pick['featured_order']; ?>
                                    </span>
                                    <img src="../uploads/destinations/<?php echo $pick['destination_image']; ?>" 
                                         class="card-img-top top-pick-img" alt="<?php echo $pick['destination_name']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($pick['destination_name']); ?></h5>
                                        
                                        <?php if ($pick['package_name'] && $pick['package_id']): ?>
                                            <div class="package-info mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-primary fw-bold">
                                                        <i class="fas fa-box"></i> <?php echo htmlspecialchars($pick['package_name']); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <span class="text-muted">
                                                        <i class="fas fa-clock"></i> <?php echo $pick['duration']; ?> days
                                                    </span>
                                                    <span class="text-success">
                                                        <i class="fas fa-tag"></i> ₱<?php echo number_format($pick['price'], 2); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="client-info">
                                            <div>
                                                <img src="../uploads/clients/<?php echo $pick['client_image'] ?: 'default-user.png'; ?>" 
                                                     class="client-image" alt="Client">
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($pick['client_name'] ?: 'Anonymous Client'); ?></div>
                                                <div class="rating-stars">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class="<?php echo ($i <= ($pick['client_rating'] ?? 5)) ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text mt-3 fst-italic">
                                            "<?php echo htmlspecialchars($pick['description']); ?>"
                                        </p>
                                        
                                        <div class="action-buttons">
                                            <a href="edit_top_picks.php?id=<?php echo $pick['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $pick['id']; ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal<?php echo $pick['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete the top pick for 
                                                <strong><?php echo htmlspecialchars($pick['destination_name']); ?></strong>?
                                                This action cannot be undone.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="" method="POST">
                                                    <input type="hidden" name="pick_id" value="<?php echo $pick['id']; ?>">
                                                    <button type="submit" name="delete_top_pick" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide success alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }, 5000);
            });
        });
    </script>
</body>
</html>

<style>
    .top-pick-card {
        transition: all 0.3s ease;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        height: 100%;
    }
    .top-pick-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .top-pick-img {
        height: 200px;
        object-fit: cover;
    }
    .client-info {
        display: flex;
        align-items: center;
        margin-top: 10px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    .client-image {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #3949ab;
        margin-right: 10px;
    }
    .rating-stars {
        color: #ffc107;
        font-size: 1rem;
    }
    .order-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #3949ab;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    .action-buttons {
        position: absolute;
        bottom: 10px;
        right: 10px;
        display: flex;
        gap: 5px;
    }
    .package-info {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 3px solid #3949ab;
    }
    .package-info i {
        width: 20px;
        text-align: center;
        margin-right: 5px;
    }
</style>