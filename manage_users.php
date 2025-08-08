<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Move these pagination variables to the top, after the database connection
include "../db.php";

// Set up pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Add this at the top of the file after include "../db.php";
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_image VARCHAR(255) DEFAULT NULL
)";
$conn->query($sql);

// Add new columns to users table
// Update the ALTER TABLE statement to include created_at
$sql = "ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL,
        ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
$conn->query($sql);

// Update the users query to order by ID only since we just added created_at
$users = $conn->query("
    SELECT * FROM users 
    WHERE role IN ('admin', 'staff')
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
");

// Replace the existing delete user handler with this updated version
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) { // Only prevent self-deletion
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First update all related bookings to set user_id to NULL
            $stmt = $conn->prepare("UPDATE bookings SET user_id = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Then update login history
            $stmt = $conn->prepare("UPDATE login_history SET user_id = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Finally delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // If everything is successful, commit the transaction
            $conn->commit();
            $_SESSION['message'] = "✅ User deleted successfully";
            
        } catch (Exception $e) {
            // If there's an error, rollback the changes
            $conn->rollback();
            $_SESSION['error'] = "❌ Error deleting user: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "❌ You cannot delete your own account";
    }
    header("Location: manage_users.php");
    exit;
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $user_id = $_POST['user_id'];
    $current_status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $new_status = $current_status == 1 ? 0 : 1;
    
    // Allow status changes for all users except the current admin
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND id != ?");
    $stmt->bind_param("iii", $new_status, $user_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ User status updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error updating user status";
    }
    header("Location: manage_users.php");
    exit;
}

// Update the users query to include all user types
$users = $conn->query("
    SELECT * FROM users 
    ORDER BY FIELD(role, 'admin', 'staff', 'user'), created_at DESC, id DESC 
    LIMIT $limit OFFSET $offset
");

// Update the total users count query
$total_users = $conn->query("
    SELECT COUNT(*) as count 
    FROM users
")->fetch_assoc()['count'];
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
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
                    <h1>Manage Users</h1>
                    <a href="add_user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add New User
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
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>User Accounts</h3>
                        <input type="text" class="form-control w-auto table-search" placeholder="Search users...">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Profile</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if($user['profile_image']): ?>
                                            <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                                     alt="Profile" class="rounded-circle" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($user['role'] != 'admin'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="status" value="<?php echo $user['status']; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $user['status'] ? 'btn-success' : 'btn-danger'; ?>">
                                                <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                   
                                    <td>
                                        <div class="btn-group">
                                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>   
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="User pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                            </li>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="delete_user" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Auto-hide alerts
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
    </script>
</body>
</html>
