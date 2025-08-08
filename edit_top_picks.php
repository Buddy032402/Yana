<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "❌ No top pick specified for editing";
    header("Location: manage_top_picks.php");
    exit;
}

$pick_id = $_GET['id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $featured_order = $_POST['featured_order'];
    $description = $_POST['description'];
    $client_name = $_POST['client_name'];
    $client_rating = $_POST['client_rating'];
    
    // Handle client image upload
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
            // Update with new image
            $stmt = $conn->prepare("UPDATE top_picks SET featured_order = ?, description = ?, client_name = ?, client_image = ?, client_rating = ? WHERE id = ?");
            $stmt->bind_param("isssii", $featured_order, $description, $client_name, $new_filename, $client_rating, $pick_id);
        } else {
            $_SESSION['error'] = "❌ Error uploading client image";
            // Continue with update without changing image
            $stmt = $conn->prepare("UPDATE top_picks SET featured_order = ?, description = ?, client_name = ?, client_rating = ? WHERE id = ?");
            $stmt->bind_param("issii", $featured_order, $description, $client_name, $client_rating, $pick_id);
        }
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE top_picks SET featured_order = ?, description = ?, client_name = ?, client_rating = ? WHERE id = ?");
        $stmt->bind_param("issii", $featured_order, $description, $client_name, $client_rating, $pick_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Top pick updated successfully";
        header("Location: manage_top_picks.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Error updating top pick: " . $conn->error;
    }
}

// Fetch top pick data
// Update the SQL query to include package information
$stmt = $conn->prepare("
    SELECT tp.*, d.name as destination_name, d.image as destination_image,
           p.name as package_name, p.price, p.duration
    FROM top_picks tp
    JOIN destinations d ON tp.destination_id = d.id
    LEFT JOIN packages p ON d.id = p.destination_id
    WHERE tp.id = ?
");
$stmt->bind_param("i", $pick_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "❌ Top pick not found";
    header("Location: manage_top_picks.php");
    exit;
}

$pick = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Top Pick - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .rating-container {
            margin-top: 10px;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .client-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #3949ab;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>

        <main class="main-content">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Edit Top Pick</h1>
                    <a href="manage_top_picks.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Top Picks
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Destination Information</h5>
                                    
                                    <div class="card mb-3">
                                        <img src="../uploads/destinations/<?php echo $pick['destination_image']; ?>" 
                                             class="card-img-top" style="height: 200px; object-fit: cover;" 
                                             alt="<?php echo $pick['destination_name']; ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($pick['destination_name']); ?></h5>
                                            <?php if ($pick['package_name']): ?>
                                                <div class="package-info mt-3">
                                                    <h6 class="mb-2"><i class="fas fa-box"></i> Package Details</h6>
                                                    <p class="mb-1">
                                                        <strong>Name:</strong> <?php echo htmlspecialchars($pick['package_name']); ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Duration:</strong> <?php echo $pick['duration']; ?> days
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>Price:</strong> ₱<?php echo number_format($pick['price'], 2); ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>Client Information</h5>
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">Client Name</label>
                                        <input type="text" class="form-control" id="client_name" name="client_name" 
                                               value="<?php echo htmlspecialchars($pick['client_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="client_image" class="form-label">Client Image</label>
                                        <input type="file" class="form-control" id="client_image" name="client_image" accept="image/*">
                                        <div class="form-text">Leave empty to keep current image</div>
                                        
                                        <?php if (!empty($pick['client_image'])): ?>
                                            <div class="mt-2">
                                                <p>Current image:</p>
                                                <img src="../uploads/clients/<?php echo $pick['client_image']; ?>" 
                                                     class="client-image" alt="Client">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Client Rating</label>
                                        <div class="rating-container">
                                            <div class="rating-stars">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?php echo ($i <= ($pick['client_rating'] ?? 5)) ? 'fas' : 'far'; ?> fa-star" data-rating="<?php echo $i; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <input type="hidden" name="client_rating" id="client_rating" value="<?php echo $pick['client_rating'] ?? 5; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="featured_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="featured_order" name="featured_order" 
                                       value="<?php echo $pick['featured_order']; ?>" required>
                                <div class="form-text">Lower numbers will appear first</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Client Testimonial</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="4" required><?php echo htmlspecialchars($pick['description']); ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <a href="manage_top_picks.php" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Top Pick
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Rating stars functionality
            const ratingStars = document.querySelectorAll('.rating-stars .fa-star');
            const ratingInput = document.getElementById('client_rating');
            
            ratingStars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    ratingInput.value = rating;
                    
                    // Update stars display
                    ratingStars.forEach(s => {
                        const starRating = s.dataset.rating;
                        if (starRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });
                
                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const rating = this.dataset.rating;
                    
                    ratingStars.forEach(s => {
                        const starRating = s.dataset.rating;
                        if (starRating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });
                
                star.addEventListener('mouseleave', function() {
                    const currentRating = ratingInput.value;
                    
                    ratingStars.forEach(s => {
                        const starRating = s.dataset.rating;
                        if (starRating <= currentRating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>


// Add this CSS
<style>
    .package-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #3949ab;
    }
    
    .package-info i {
        color: #3949ab;
        margin-right: 8px;
    }
</style>