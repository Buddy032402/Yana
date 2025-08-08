<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Get all archived messages
$messages_query = "SELECT * FROM admin_messages WHERE status = 'archived' ORDER BY created_at DESC";
$messages = $conn->query($messages_query);
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Messages - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .message-list {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            border-radius: 8px;
        }
        
        .message-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            padding: 15px;
            margin-bottom: 8px;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .message-item:hover {
            background-color: rgba(63, 81, 181, 0.08);
            border-left-color: #3f51b5;
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .message-item .sender-name {
            font-weight: 600;
            color: #333;
        }
        
        .message-item .message-date {
            font-size: 0.85rem;
            color: #666;
            background-color: rgba(0, 0, 0, 0.05);
            padding: 3px 8px;
            border-radius: 12px;
        }
        
        .message-item .message-subject {
            font-weight: 500;
            margin: 8px 0;
            color: #444;
        }
        
        .message-item .message-preview {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-restore {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-restore:hover {
            background: linear-gradient(135deg, #2E7D32, #1B5E20);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #F44336, #C62828);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #C62828, #B71C1C);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #1a237e, #3f51b5);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #666;
        }
        
        .empty-state i {
            color: #bbb;
            margin-bottom: 15px;
        }
        
        .empty-state h5 {
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #888;
            max-width: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1><i class="fas fa-archive me-2"></i>Archived Messages</h1>
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

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-archive me-2"></i>Archived Messages</h5>
                            <a href="inbox.php" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-inbox me-1"></i> Back to Inbox
                            </a>
                        </div>
                        <div class="card-body p-3">
                            <div class="list-group list-group-flush message-list">
                                <?php if ($messages && $messages->num_rows > 0): ?>
                                    <?php while($msg = $messages->fetch_assoc()): ?>
                                        <div class="list-group-item list-group-item-action message-item">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <h6 class="mb-1 sender-name"><?php echo htmlspecialchars($msg['sender_name']); ?></h6>
                                                <small class="message-date"><?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 message-subject"><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></p>
                                            <small class="text-muted message-preview"><?php echo htmlspecialchars(substr($msg['message'], 0, 100)) . (strlen($msg['message']) > 100 ? '...' : ''); ?></small>
                                            <div class="message-actions">
                                                <button class="btn btn-sm btn-restore" onclick="restoreMessage(<?php echo $msg['id']; ?>)">
                                                    <i class="fas fa-inbox me-1"></i> Restore to Inbox
                                                </button>
                                                <button class="btn btn-sm btn-delete" onclick="deleteMessage(<?php echo $msg['id']; ?>)">
                                                    <i class="fas fa-trash me-1"></i> Delete Permanently
                                                </button>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-archive fa-3x"></i>
                                        <h5>No archived messages</h5>
                                        <p>When you archive messages, they will appear here</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteMessage(id) {
            if (confirm('Are you sure you want to permanently delete this message?')) {
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
                        window.location.reload();
                    } else {
                        alert('Error deleting message: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the message');
                });
            }
        }

        function restoreMessage(id) {
            fetch('message_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=restore&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'inbox.php';
                } else {
                    alert('Error restoring message: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while restoring the message');
            });
        }
    </script>
</body>
</html>