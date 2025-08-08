<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Get sent messages
$sender_name = $_SESSION['admin_name'] ?? 'Admin';
$sender_email = $_SESSION['admin_email'] ?? 'admin@yana.com';

$query = "SELECT * FROM admin_messages 
          WHERE sender_name = ? OR sender_email = ?
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $sender_name, $sender_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sent Messages - Admin Dashboard</title>
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
                    <h1>Sent Messages</h1>
                    <div class="message-nav">
                        <a href="inbox.php" class="btn btn-outline-primary">
                            <i class="fas fa-inbox"></i> Inbox
                        </a>
                        <a href="sent_messages.php" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Sent
                        </a>
                        <a href="archived_messages.php" class="btn btn-outline-primary">
                            <i class="fas fa-archive"></i> Archive
                        </a>
                        <a href="compose_message.php" class="btn btn-success">
                            <i class="fas fa-pen"></i> Compose
                        </a>
                    </div>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>To</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($message = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['recipient_email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $message['is_read'] ? 'success' : 'warning'; ?>">
                                            <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button onclick="deleteMessage(<?php echo $message['id']; ?>)" 
                                                    class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteMessage(id) {
            if (confirm('Are you sure you want to delete this message?')) {
                fetch('message_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>