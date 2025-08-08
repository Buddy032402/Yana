<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Get all users and contact form senders for recipient selection
$users_query = "SELECT DISTINCT 
    COALESCE(u.id, NULL) as id,
    COALESCE(u.name, am.sender_name) as name,
    COALESCE(u.email, am.sender_email) as email
FROM users u
RIGHT JOIN admin_messages am ON u.email = am.sender_email
WHERE am.sender_email IS NOT NULL
ORDER BY name ASC";
$users = $conn->query($users_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_email = $_POST['recipient_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Get admin name and email from session
    $sender_name = $_SESSION['admin_name'] ?? 'Admin';
    $sender_email = $_SESSION['admin_email'] ?? 'admin@yana.com';
    
    $stmt = $conn->prepare("INSERT INTO admin_messages (sender_name, sender_email, subject, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param("ssss", $sender_name, $sender_email, $subject, $message);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Message sent successfully!";
        header("Location: sent_messages.php");
        exit;
    } else {
        $_SESSION['error'] = "Error sending message.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .compose-form {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .card-header {
            background: linear-gradient(135deg, #1a237e, #3f51b5);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        .form-control:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 0 0.2rem rgba(63,81,181,0.25);
        }
        .btn-send {
            background: linear-gradient(135deg, #1a237e, #3f51b5);
            border: none;
            padding: 10px 30px;
            border-radius: 5px;
        }
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(63,81,181,0.3);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <div class="compose-form">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Compose New Message</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">To:</label>
                                <select name="recipient_id" class="form-control select2" required>
                                    <option value="">Select Recipient</option>
                                    <?php while($user = $users->fetch_assoc()): ?>
                                        <option value="<?php echo $user['email']; ?>">
                                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject:</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message:</label>
                                <textarea name="message" class="form-control" rows="10" required></textarea>
                            </div>
                            <div class="text-end">
                                <a href="manage_messages.php" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-send">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });
    </script>
</body>
</html>