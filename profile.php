<?php
session_start();
if (!isset($_SESSION["admin"]) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$message = "";
$user_id = $_SESSION['user_id'];

// Fetch user data and verify it exists
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST["name"] ?? $user['name']);
        $email = trim($_POST["email"] ?? $user['email']);
        
        // Check if email exists (excluding current user)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "❌ Email already exists";
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['admin'] = $name; // Update session with new name
                $message = "✅ Profile updated successfully";
                $user['name'] = $name;
                $user['email'] = $email;
            } else {
                $message = "❌ Error updating profile";
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];
        
        if (!password_verify($current_password, $user['password'])) {
            $message = "❌ Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $message = "❌ New passwords do not match";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $message = "✅ Password changed successfully";
            } else {
                $message = "❌ Error changing password";
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
    <title>Profile - Admin Dashboard</title>
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
                <h1>Profile Settings</h1>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Update Profile</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required
                                       pattern="[a-zA-Z0-9\s_]{3,50}"
                                       value="<?php echo htmlspecialchars($user['name']); ?>">
                                <div class="invalid-feedback">
                                    Please enter a valid name (3-50 characters, letters, numbers, spaces, and underscores only)
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                                <div class="invalid-feedback">
                                    Please enter a valid email address
                                </div>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <div class="input-group">
                                    <input type="password" name="current_password" class="form-control" 
                                           id="current_password" required>
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" name="new_password" class="form-control" 
                                           id="new_password" required
                                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Password must be at least 8 characters long and contain at least one letter and one number
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" class="form-control" 
                                           id="confirm_password" required>
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
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
</body>
</html>