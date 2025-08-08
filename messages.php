<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";
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
    <link rel="stylesheet" href="css/message-styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/messages.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include "includes/sidebar.php"; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-content">
                    <h1>Messages</h1>
                    <a href="compose_message.php" class="btn btn-primary">
                        <i class="fas fa-pen"></i> Compose New Message
                    </a>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-primary" onclick="loadInbox()">
                            <i class="fas fa-inbox"></i> Inbox
                        </button>
                        <button class="btn btn-secondary" onclick="loadArchived()">
                            <i class="fas fa-archive"></i> Archived
                        </button>
                        <button class="btn btn-info" onclick="loadSent()">
                            <i class="fas fa-paper-plane"></i> Sent
                        </button>
                    </div>
                    <div id="messageContainer" class="message-container"></div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>