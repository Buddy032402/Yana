<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Update the query to use the correct columns from the testimonials table
$stmt = $conn->prepare("
    SELECT t.*, p.name as package_name 
    FROM testimonials t
    LEFT JOIN packages p ON t.package_id = p.id
    WHERE t.id = ?
");

$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$testimonial = $result->fetch_assoc();

if (!$testimonial) {
    $_SESSION['error'] = "Testimonial not found";
    header("Location: manage_testimonials.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Testimonial - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .testimonial-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            padding: 2rem;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-section h5 {
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 1.2rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }
        .star-rating {
            color: #fbbf24;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }
        .content-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4b5563;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .action-buttons .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .badge {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1><i class="fas fa-comment-alt me-2"></i>View Testimonial</h1>
                    <a href="manage_testimonials.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </header>

            <div class="testimonial-card">
                <div class="row">
                    <div class="col-md-3">
                        <?php if($testimonial['image']): ?>
                            <img src="../uploads/testimonials/<?php echo $testimonial['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($testimonial['customer_name']); ?>"
                                 class="img-fluid rounded-circle mb-3">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($testimonial['customer_name'] ?? 'A', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-section">
                    <h5><i class="fas fa-user me-2"></i>Customer Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($testimonial['customer_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($testimonial['customer_email']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h5><i class="fas fa-info-circle me-2"></i>Testimonial Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Rating:</strong> 
                                <span class="star-rating"><?php echo str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']); ?></span>
                            </p>
                            <p><strong>Package:</strong> <?php echo htmlspecialchars($testimonial['package_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $testimonial['status'] == 'approved' ? 'success' : 
                                        ($testimonial['status'] == 'pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <i class="fas fa-<?php 
                                        echo $testimonial['status'] == 'approved' ? 'check' : 
                                            ($testimonial['status'] == 'pending' ? 'clock' : 'times'); 
                                    ?>"></i>
                                    <?php echo ucfirst($testimonial['status']); ?>
                                </span>
                            </p>
                            <p><strong>Date:</strong> <i class="far fa-calendar-alt me-1"></i><?php echo date('F d, Y h:i A', strtotime($testimonial['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h5><i class="fas fa-quote-left me-2"></i>Testimonial Content</h5>
                    <div class="content-box">
                        <?php echo nl2br(htmlspecialchars($testimonial['content'])); ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="edit_testimonial.php?id=<?php echo $testimonial['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Testimonial
                    </a>
                    <button onclick="deleteTestimonial(<?php echo $testimonial['id']; ?>)" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Testimonial
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
    function deleteTestimonial(id) {
        if (confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')) {
            window.location.href = `delete_testimonial.php?id=${id}`;
        }
    }
    </script>
</body>
</html>