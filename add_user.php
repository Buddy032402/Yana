<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../db.php";

$message = "";

// Modify the POST handling section
// After successful user insertion in add_user.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    
    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    
    if ($result->num_rows > 0) {
        $message = "❌ Email address already exists";
    } else {
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $role = $_POST["role"];
        $status = isset($_POST["status"]) ? 1 : 0;

        // Handle profile image upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "../uploads/profiles/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $profile_image = uniqid() . '.' . $file_extension;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_dir . $profile_image);
        }

        // Insert new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashed_password, $role, $status, $profile_image);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ User added successfully";
            header("Location: manage_users.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Admin Dashboard</title>
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
                <h1>Add New User</h1>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                    
                    <!-- Add this new field after the email input -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/*">
                            <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF. Max size: 2MB</div>
                        </div>
                        <div class="col-md-6">
                            <div id="imagePreview" class="mt-2" style="max-width: 200px;">
                                <!-- Preview will be shown here -->
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required
                                   pattern="[a-zA-Z0-9_]{3,20}"
                                   title="Username must be between 3 and 20 characters and can only contain letters, numbers, and underscores">
                            <div class="invalid-feedback">
                                Please enter a valid username (3-20 characters, letters, numbers, and underscores only)
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password" required
                                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                                       title="Password must be at least 8 characters long and contain at least one letter and one number">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Password must be at least 8 characters long and contain at least one letter and one number
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Please confirm your password
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="user">User</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="status" class="form-check-input" id="status" checked>
                                <label class="form-check-label" for="status">Active Account</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin.js"></script>
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.type === 'password' ? 'text' : 'password';
    field.type = type;
    
    const icon = field.nextElementSibling.querySelector('i');
    icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
// Add this before </body> tag
?>
<script>
// Auto-hide alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    
    if (password.value !== confirm.value) {
        e.preventDefault();
        showAlert('❌ Passwords do not match');
        return false;
    }
});

// Show temporary alert function
function showAlert(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert ' + (message.includes('✅') ? 'alert-success' : 'alert-danger');
    alertDiv.textContent = message;
    
    document.querySelector('.card').insertBefore(alertDiv, document.querySelector('.card-body'));
    
    setTimeout(() => {
        alertDiv.style.transition = 'opacity 0.5s ease';
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}
</script>
document.querySelector('input[name="profile_image"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="Profile Preview">`;
        }
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});
</script>
</body>
</html>