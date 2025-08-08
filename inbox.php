<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Check if a message was deleted
if (isset($_GET['deleted']) && $_GET['deleted'] === 'true') {
    $_SESSION['message'] = "✅ Message deleted successfully";
}

// Mark message as read if viewing a specific message
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $message_id = (int)$_GET['view'];
    
    // Update the message status to read
    $stmt = $conn->prepare("UPDATE admin_messages SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    
    // Get the message details
    $stmt = $conn->prepare("SELECT * FROM admin_messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message = $result->fetch_assoc();
    
    if (!$message) {
        $_SESSION['error'] = "Message not found";
        header("Location: inbox.php");
        exit;
    }
}

// Get all messages for the inbox
$messages_query = "SELECT * FROM admin_messages WHERE status = 'active' OR status IS NULL ORDER BY created_at DESC";
$messages = $conn->query($messages_query);
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - Admin Dashboard</title>
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
        }
        
        .message-item:hover {
            background-color: rgba(63, 81, 181, 0.05);
            border-left-color: #3f51b5;
            transform: translateX(5px);
        }
        
        .message-item.unread {
            background-color: rgba(63, 81, 181, 0.1);
            border-left-color: #1a237e;
        }
        
        .message-content {
            white-space: pre-wrap;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .message-meta {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .message-actions {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 30px;
        }
        
        .message-actions .btn {
            margin-right: 10px;
            padding: 8px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .message-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .message-actions .btn-primary {
            background: linear-gradient(135deg, #1a237e, #3f51b5);
            border: none;
        }
        
        .message-actions .btn-danger {
            background: linear-gradient(135deg, #d32f2f, #f44336);
            border: none;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #1a237e, #3f51b5);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .list-group-item:first-child {
            border-radius: 0;
        }
        
        .text-muted {
            color: #666 !important;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1>Inbox</h1>
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

            <!-- Add auto-dismiss script for success and error messages -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Auto-dismiss success messages
                    const successMessage = document.querySelector('.alert-success');
                    if (successMessage) {
                        setTimeout(function() {
                            successMessage.style.transition = 'opacity 0.5s ease';
                            successMessage.style.opacity = '0';
                            setTimeout(function() {
                                successMessage.style.display = 'none';
                            }, 500);
                        }, 3000);
                    }
                    
                    // Auto-dismiss error messages
                    const errorMessage = document.querySelector('.alert-danger');
                    if (errorMessage) {
                        setTimeout(function() {
                            errorMessage.style.transition = 'opacity 0.5s ease';
                            errorMessage.style.opacity = '0';
                            setTimeout(function() {
                                errorMessage.style.display = 'none';
                            }, 500);
                        }, 3000);
                    }
                });
            </script>

            <div class="row">
                <!-- Message list -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Messages</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush message-list">
                                <?php if ($messages && $messages->num_rows > 0): ?>
                                    <?php while($msg = $messages->fetch_assoc()): ?>
                                        <a href="inbox.php?view=<?php echo $msg['id']; ?>" 
                                           class="list-group-item list-group-item-action message-item <?php echo (!$msg['is_read']) ? 'unread' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($msg['sender_name']); ?></h6>
                                                <small><?php echo date('M d, h:i A', strtotime($msg['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 text-truncate"><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></p>
                                            <small class="text-muted text-truncate d-block"><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?></small>
                                        </a>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="list-group-item">No messages found</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Message content -->
                <div class="col-md-8">
                    <div class="card">
                        <?php if (isset($message)): ?>
                            <div class="card-header">
                                <h5 class="mb-0"><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="message-meta mb-3">
                                    <div><strong>From:</strong> <?php echo htmlspecialchars($message['sender_name']); ?> (<?php echo htmlspecialchars($message['sender_email']); ?>)</div>
                                    <?php if (!empty($message['phone'])): ?>
                                    <div><strong>Phone:</strong> <?php echo htmlspecialchars($message['phone']); ?></div>
                                    <?php endif; ?>
                                    <div><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($message['created_at'])); ?></div>
                                </div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                                
                                <div class="message-actions mt-4">
                                    <!-- <a href="compose_message.php?reply_to=<?php echo htmlspecialchars($message['sender_email']); ?>&subject=<?php echo urlencode('Re: ' . ($message['subject'] ?: 'No Subject')); ?>" class="btn btn-primary">
                                        <i class="fas fa-reply"></i> Reply
                                    </a> -->
                                    <button class="btn btn-danger" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <button class="btn btn-secondary" onclick="archiveMessage(<?php echo $message['id']; ?>)">
                                        <i class="fas fa-archive"></i> Archive
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card-body text-center py-5">
                                <i class="fas fa-envelope fa-3x mb-3 text-muted"></i>
                                <h5>Select a message to view</h5>
                            </div>
                        <?php endif; ?>
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
                        // Set success message in session and redirect
                        window.location.href = 'inbox.php?deleted=true';
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

        function archiveMessage(id) {
            if (confirm('Are you sure you want to archive this message?')) {
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
                        window.location.href = 'archived_messages.php';
                    } else {
                        alert('Error archiving message: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while archiving the message');
                });
            }
        }
    </script>
</body>
</html>