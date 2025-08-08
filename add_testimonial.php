<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Update the SQL structure
$sql = "ALTER TABLE testimonials 
        MODIFY customer_name VARCHAR(100) NOT NULL,
        MODIFY customer_email VARCHAR(255),
        MODIFY content TEXT NOT NULL,
        MODIFY rating INT NOT NULL,
        MODIFY status VARCHAR(20) DEFAULT 'pending',
        MODIFY package_id INT,
        MODIFY image VARCHAR(255) DEFAULT NULL,
        MODIFY created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$conn->query($sql);
// Remove the above code after running once

// Add image column to database
$sql = "ALTER TABLE testimonials ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL";
$conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Existing code remains unchanged
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $content = $_POST['content'];
    $rating = $_POST['rating'];
    $status = $_POST['status'];
    $package_id = $_POST['package_id'] ?: null;
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            $upload_path = "../uploads/testimonials/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $image)) {
                // Image uploaded successfully
            } else {
                $_SESSION['error'] = "❌ Error uploading image";
                $image = '';
            }
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO testimonials (customer_name, customer_email, content, rating, status, package_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisss", $customer_name, $customer_email, $content, $rating, $status, $package_id, $image);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Testimonial added successfully";
        header("Location: manage_testimonials.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Error adding testimonial";
    }
}

// Fetch packages for dropdown
$packages = $conn->query("SELECT id, name FROM packages WHERE status = 1 ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Testimonial - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Enhanced styling for the add testimonial page */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            border-bottom: none;
            padding: 20px 25px;
        }
        
        .card-header h1 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.15);
            border-color: #4e73df;
        }
        
        .btn {
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4e73df, #224abe);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #224abe, #1a3a8f);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(34, 74, 190, 0.3);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            color: #495057;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            color: #212529;
            transform: translateY(-2px);
        }
        
        .input-group-text {
            cursor: pointer;
            background-color: #f8f9fa;
            border-color: #e0e0e0;
            transition: all 0.3s ease;
            border-radius: 0 8px 8px 0;
            padding: 12px 15px;
        }
        
        .input-group-text:hover {
            background-color: #e9ecef;
        }
        
        #imagePreview {
            transition: all 0.5s ease;
            margin-top: 15px;
        }
        
        #imagePreview img {
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            max-height: 200px;
            object-fit: cover;
        }
        
        #imagePreview img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Star rating styling */
        .rating-stars {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .rating-stars i {
            color: #ffc107;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .rating-stars i:hover {
            transform: scale(1.2);
        }
        
        /* Form section styling */
        .form-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #4e73df;
            transition: all 0.3s ease;
        }
        
        .form-section:hover {
            background: #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transform: translateX(5px);
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 15px;
        }
        
        /* Animation for form elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-form > div {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }
        
        .animate-form > div:nth-child(1) { animation-delay: 0.1s; }
        .animate-form > div:nth-child(2) { animation-delay: 0.2s; }
        .animate-form > div:nth-child(3) { animation-delay: 0.3s; }
        .animate-form > div:nth-child(4) { animation-delay: 0.4s; }
        .animate-form > div:nth-child(5) { animation-delay: 0.5s; }
        .animate-form > div:nth-child(6) { animation-delay: 0.6s; }
        .animate-form > div:nth-child(7) { animation-delay: 0.7s; }
        .animate-form > div:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <div class="container py-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-comment-dots me-2"></i>
                        <h1 class="h3 mb-0">Add New Testimonial</h1>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation animate-form" enctype="multipart/form-data" novalidate>
                        <!-- Customer Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-user me-2"></i>Customer Information
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customer_name" class="form-label">Customer Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_email" class="form-label">Customer Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial Details Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-star me-2"></i>Testimonial Details
                            </div>
                            <div class="mb-3">
                                <label for="package_id" class="form-label">Related Package</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-box"></i></span>
                                    <select class="form-select" id="package_id" name="package_id">
                                        <option value="">Select a package</option>
                                        <?php while($package = $packages->fetch_assoc()): ?>
                                            <option value="<?php echo $package['id']; ?>">
                                                <?php echo htmlspecialchars($package['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating *</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?php echo $i; ?>">
                                            <?php echo str_repeat('⭐', $i); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <div class="rating-stars mt-2" id="ratingStarsDisplay">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Testimonial Content *</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Upload Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fas fa-image me-2"></i>Customer Image
                            </div>
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                                    <label class="input-group-text" for="image"><i class="fas fa-upload me-2"></i>Upload</label>
                                </div>
                                <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i>Supported formats: JPG, JPEG, PNG, WebP (Max size: 2MB)</div>
                            </div>

                            <!-- Image preview container -->
                            <div class="mb-3 d-none" id="imagePreview">
                                <img src="" alt="Preview" class="img-thumbnail">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="manage_testimonials.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Add Testimonial
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
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Image preview functionality
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    const file = e.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('d-none');
    }
});

// Enhanced rating stars display
document.getElementById('rating').addEventListener('change', function() {
    const rating = parseInt(this.value);
    const stars = document.querySelectorAll('#ratingStarsDisplay i');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('text-warning');
        } else {
            star.classList.remove('text-warning');
        }
    });
});

// Initialize rating stars on page load
document.addEventListener('DOMContentLoaded', function() {
    const rating = parseInt(document.getElementById('rating').value);
    const stars = document.querySelectorAll('#ratingStarsDisplay i');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('text-warning');
        } else {
            star.classList.remove('text-warning');
        }
    });
    
    // Add click functionality to stars
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            document.getElementById('rating').value = index + 1;
            
            // Update visual display
            stars.forEach((s, i) => {
                if (i <= index) {
                    s.classList.add('text-warning');
                } else {
                    s.classList.remove('text-warning');
                }
            });
        });
    });
});
</script>
</body>
</html> 