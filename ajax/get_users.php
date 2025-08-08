<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

include "../../db.php";

// Handle AJAX delete request
if (isset($_POST['delete_ajax_user'])) {
    $user_id = $_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "self";
    }
    exit;
}

$users = $conn->query("
    SELECT * FROM users 
    WHERE role IN ('admin', 'staff')
    ORDER BY id DESC
");

while($user = $users->fetch_assoc()): ?>
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
            <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['status']; ?>)" 
                    class="btn btn-sm <?php echo $user['status'] ? 'btn-success' : 'btn-danger'; ?>">
                <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
            </button>
        </td>
        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
        <td>
            <div class="btn-group">
                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                </a>
                <?php if($user['id'] != $_SESSION['user_id']): ?>
                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php endwhile; ?>

<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('ajax/get_users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'delete_ajax_user=' + userId + '&user_id=' + userId
        })
        .then(response => response.text())
        .then(result => {
            if (result === 'success') {
                location.reload();
            } else if (result === 'self') {
                alert('You cannot delete your own account');
            } else {
                alert('Error deleting user');
            }
        });
    }
}

function toggleUserStatus(userId, currentStatus) {
    fetch('ajax/toggle_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + userId + '&status=' + currentStatus
    })
    .then(response => response.text())
    .then(result => {
        if (result === 'success') {
            location.reload();
        } else {
            alert('Error updating user status');
        }
    });
}
</script>