<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../db.php";

$message = "";
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $role = $_POST["role"];
    $status = isset($_POST["status"]) ? 1 : 0;
    
    // Handle image upload
    $profile_image = $user['profile_image']; // Keep existing image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = "../uploads/profiles/";
            
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path . $new_filename)) {
                // Delete old image if exists
                if ($user['profile_image'] && file_exists($upload_path . $user['profile_image'])) {
                    unlink($upload_path . $user['profile_image']);
                }
                $profile_image = $new_filename;
            }
        }
    }

    // Check if password is being updated
    $password_sql = "";
    $types = "sssisi";
    $params = [$name, $email, $role, $status, $profile_image, $user_id];

    if (!empty($_POST["password"])) {
        if ($_POST["password"] !== $_POST["confirm_password"]) {
            $message = "❌ Passwords do not match";
        } else {
            $hashed_password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $password_sql = ", password = ?";
            $types = "sssissi";
            array_splice($params, -1, 0, [$hashed_password]);
        }
    }

    if (!$message) {
        // Check if email exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "❌ Email already exists";
        } else {
            // Update user
            $sql = "UPDATE users SET name = ?, email = ?, role = ?, status = ?, profile_image = ? $password_sql WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "✅ User updated successfully";
                header("Location: manage_users.php");
                exit;
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
        }
    }
} // Added missing closing brace for POST check
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Dashboard</title>
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
                    <h1>Edit User</h1>
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Profile Image</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF. Max size: 2MB</div>
                            </div>
                            <div class="col-md-6">
                                <div id="imagePreview" class="mt-2" style="max-width: 200px;">
                                    <?php if($user['profile_image']): ?>
                                        <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                             class="img-fluid rounded" alt="Current Profile">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required
                                       value="<?php echo htmlspecialchars($user['name']); ?>"
                                       pattern="[a-zA-Z0-9_]{3,20}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password (leave blank to keep current)</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" id="password"
                                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" class="form-control" id="confirm_password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="status" class="form-check-input" id="status"
                                           <?php echo $user['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Active Account</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update User</button>
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

    document.querySelector('input[name="profile_image"]').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" alt="Profile Preview">`;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>