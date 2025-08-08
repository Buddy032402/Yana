<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Update the query to use the correct columns
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rating = $_POST['rating'];
    $content = $_POST['content'];
    $status = $_POST['status'];
    $package_id = $_POST['package_id'] ?: null;
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];

    $update_stmt = $conn->prepare("
        UPDATE testimonials 
        SET rating = ?, 
            content = ?, 
            status = ?, 
            package_id = ?,
            customer_name = ?,
            customer_email = ?
        WHERE id = ?
    ");
    
    $update_stmt->bind_param("isssisi", 
        $rating, 
        $content, 
        $status, 
        $package_id,
        $customer_name,
        $customer_email,
        $_GET['id']
    );

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Testimonial updated successfully";
        header("Location: manage_testimonials.php");
        exit;
    } else {
        $_SESSION['error'] = "Error updating testimonial";
    }

    // Handle image update
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            $upload_path = "../uploads/testimonials/";
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path . $image)) {
                // Delete old image if exists
                if (!empty($testimonial['image'])) {
                    @unlink($upload_path . $testimonial['image']);
                }
                
                // Update image in database
                $update_stmt = $conn->prepare("UPDATE testimonials SET image = ? WHERE id = ?");
                $update_stmt->bind_param("si", $image, $_GET['id']);
                $update_stmt->execute();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Testimonial - Admin Dashboard</title>
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
                    <h1>Edit Testimonial</h1>
                    <a href="manage_testimonials.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </header>

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
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="customer_name"
                                   value="<?php echo htmlspecialchars($testimonial['customer_name'] ?? ''); ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Customer Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="customer_email"
                                   value="<?php echo htmlspecialchars($testimonial['customer_email'] ?? ''); ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="package_id" class="form-label">Package</label>
                            <select class="form-select" id="package_id" name="package_id">
                                <option value="">Select Package</option>
                                <?php 
                                $packages = $conn->query("SELECT id, name FROM packages WHERE status = 1");
                                while($package = $packages->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $package['id']; ?>" 
                                            <?php echo ($testimonial['package_id'] == $package['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($package['name']); ?>
                                            </option>
                                    <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Rest of the form fields remain the same -->
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $testimonial['rating'] == $i ? 'selected' : ''; ?>>
                                        <?php echo str_repeat('⭐', $i); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($testimonial['content']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?php echo $testimonial['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $testimonial['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $testimonial['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Testimonial
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>