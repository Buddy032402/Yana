<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$message_id) {
    header("Location: manage_messages.php");
    exit;
}

// Mark message as read
$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();

// Get message details
$query = "SELECT 
    m.*,
    COALESCE(s.name, 'Contact Form User') as sender_name,
    COALESCE(s.email, 'N/A') as sender_email,
    COALESCE(s.phone, 'N/A') as sender_phone
    FROM messages m
    LEFT JOIN users s ON m.sender_id = s.id
    WHERE m.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();

if (!$message) {
    header("Location: manage_messages.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .message-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 2.5rem;
            margin: 20px;
            transition: all 0.3s ease;
        }

        .message-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .message-header h2 {
            color: #2563eb;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .message-meta {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
            background: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: inline-block;
        }

        .message-content {
            white-space: pre-wrap;
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
            line-height: 1.8;
            color: #334155;
            font-size: 1.1rem;
        }

        .sender-info {
            background: linear-gradient(145deg, #f8fafc, #f1f5f9);
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .sender-info h4 {
            color: #2563eb;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
        }

        .sender-info p {
            margin-bottom: 0.75rem;
            color: #475569;
        }

        .sender-info strong {
            color: #334155;
            min-width: 100px;
            display: inline-block;
        }

        .mt-4 {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background: #f59e0b;
            border: none;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            border: none;
            margin-left: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .dashboard-header {
            background: #fff;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            color: #1e293b;
            font-size: 1.8rem;
            margin: 0;
        }

        .btn-secondary {
            background: #64748b;
            border: none;
            padding: 0.75rem 1.5rem;
        }

        .btn-secondary:hover {
            background: #475569;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>View Message</h1>
                <a href="manage_messages.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
            </div>
        </header>

        <div class="message-container">
            <div class="message-header">
                <h2><?php echo htmlspecialchars($message['subject'] ?: 'Contact Form Message'); ?></h2>
                <div class="message-meta">
                    From: <?php echo htmlspecialchars($message['sender_name']); ?> 
                    (<?php echo htmlspecialchars($message['sender_email']); ?>)
                    <br>
                    Date: <?php echo date('F d, Y H:i:s', strtotime($message['created_at'])); ?>
                </div>
            </div>

            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>

            <div class="sender-info">
                <h4>Sender Information</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($message['sender_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($message['sender_email']); ?></p>
                <?php if (!empty($message['sender_phone'])): ?>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($message['sender_phone']); ?></p>
                <?php endif; ?>
            </div>

            <div class="mt-4">
                <button onclick="archiveMessage(<?php echo $message_id; ?>)" class="btn btn-warning">
                    <i class="fas fa-archive"></i> Archive Message
                </button>
                <button onclick="deleteMessage(<?php echo $message_id; ?>)" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Message
                </button>
            </div>
        </div>
    </main>
</div>

<script>
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
                window.location.href = 'manage_messages.php';
            }
        });
    }
}

function deleteMessage(id) {
    if (confirm('Are you sure you want to delete this message? This cannot be undone.')) {
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
                window.location.href = 'manage_messages.php';
            }
        });
    }
}
</script>

</body>
</html>