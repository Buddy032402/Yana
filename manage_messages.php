<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Get messages for admin
$user_id = $_SESSION['user_id'];
$query = "SELECT 
    m.*, 
    u.name as sender_name,
    u.email as sender_email,
    CASE 
        WHEN m.subject = '' OR m.subject IS NULL THEN 'Contact Form Message'
        ELSE m.subject 
    END as display_subject
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.recipient_id = ? 
    AND m.is_archived = 0
    ORDER BY m.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Dashboard</title>
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
                <h1>Messages</h1>
                <div class="message-nav">
                    <a href="inbox.php" class="btn btn-outline-primary">
                        <i class="fas fa-inbox"></i> Inbox
                    </a>
                    <a href="sent_messages.php" class="btn btn-outline-primary">
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

        <style>
            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 1rem;
            }

            .message-nav {
                display: flex;
                gap: 10px;
            }

            .message-nav .btn {
                padding: 8px 16px;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.3s ease;
                border-radius: 20px;
            }

            .message-nav .btn i {
                font-size: 14px;
            }

            .message-nav .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }

            .btn-primary {
                background: linear-gradient(135deg, #1a237e, #3f51b5);
                border: none;
            }

            .btn-outline-primary {
                color: #1a237e;
                border: 2px solid #1a237e;
                background: transparent;
            }

            .btn-outline-primary:hover {
                background: linear-gradient(135deg, #1a237e, #3f51b5);
                color: white;
                border: 2px solid transparent;
            }

            .btn-success {
                background: linear-gradient(135deg, #2e7d32, #43a047);
                border: none;
            }
        </style>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['delete_message'])): ?>
            <?php echo $_SESSION['delete_message']; ?>
            <?php unset($_SESSION['delete_message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($message = $result->fetch_assoc()): ?>
                            <tr class="<?php echo $message['is_read'] ? '' : 'fw-bold'; ?>">
                                <td>
                                    <i class="fas <?php echo $message['is_read'] ? 'fa-envelope-open text-muted' : 'fa-envelope text-primary'; ?>"></i>
                                </td>
                                <td><?php echo htmlspecialchars($message['sender_name']); ?></td>
                                <td><?php echo htmlspecialchars($message['display_subject']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button onclick="markMessage(<?php echo $message['id']; ?>, '<?php echo $message['is_read'] ? 'unread' : 'read'; ?>')" 
                                                class="btn btn-sm btn-secondary">
                                            <i class="fas fa-<?php echo $message['is_read'] ? 'envelope' : 'envelope-open'; ?>"></i>
                                        </button>
                                        <button onclick="archiveMessage(<?php echo $message['id']; ?>)" 
                                                class="btn btn-sm btn-warning">
                                            <i class="fas fa-archive"></i>
                                        </button>
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
    function markMessage(id, action) {
        fetch('message_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=mark_${action}&id=${id}`
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

    function archiveMessage(id) {
        if (confirm('Archive this message?')) {
            fetch('message_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=archive&id=${id}`
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