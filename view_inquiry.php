<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";
include "../config.php"; // Add this line to include the configuration file

$message = "";
$inquiry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch inquiry details
$stmt = $conn->prepare("SELECT i.*, p.name as package_name 
                       FROM inquiries i 
                       LEFT JOIN packages p ON i.tour_id = p.id 
                       WHERE i.id = ?");
$stmt->bind_param("i", $inquiry_id);
$stmt->execute();
$inquiry = $stmt->get_result()->fetch_assoc();

// Add null checks for response fields
$inquiry['response'] = $inquiry['response'] ?? null;
$inquiry['responded_at'] = $inquiry['responded_at'] ?? null;
$inquiry['responded_by'] = $inquiry['responded_by'] ?? null;

if (!$inquiry) {
    header("Location: manage_inquiries.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $inquiry_id);
    if ($stmt->execute()) {
        $message = "✅ Status updated successfully";
        // Refresh inquiry data
        $stmt = $conn->prepare("SELECT i.*, p.name as package_name 
                              FROM inquiries i 
                              LEFT JOIN packages p ON i.tour_id = p.id 
                              WHERE i.id = ?");
        $stmt->bind_param("i", $inquiry_id);
        $stmt->execute();
        $inquiry = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "❌ Error updating status";
    }
}

// If this is a booking inquiry, fetch additional details
$booking_details = null;
if ($inquiry['type'] == 'booking' && $inquiry['tour_id']) {
    $stmt = $conn->prepare("SELECT * FROM booking_inquiries 
                           WHERE email = ? AND package_id = ? 
                           ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("si", $inquiry['email'], $inquiry['tour_id']);
    $stmt->execute();
    $booking_details = $stmt->get_result()->fetch_assoc();
}

// Handle response submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = trim($_POST["response"]);
    $status = $_POST["status"];
    
    $stmt = $conn->prepare("UPDATE inquiries SET response = ?, status = ?, responded_at = NOW(), responded_by = ? WHERE id = ?");
    $stmt->bind_param("sssi", $response, $status, $_SESSION['username'], $inquiry_id);
    
    if ($stmt->execute()) {
        // Load PHPMailer
        require_once '../PHPMailer/src/PHPMailer.php';
        require_once '../PHPMailer/src/SMTP.php';
        require_once '../PHPMailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'marcjullanp@gmail.com'; // Your Gmail
            $mail->Password   = 'your_app_password'; // App password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            
            // Recipients
            $mail->setFrom('your@gmail.com', 'Yana Byahe Na Travel and Tours');
            $mail->addAddress($inquiry['email'], $inquiry['name']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Response to your inquiry - " . htmlspecialchars($inquiry['package_name'] ?? 'General Inquiry');
            $mail->Body    = "<p>Dear " . htmlspecialchars($inquiry['name']) . ",</p>
                            <p>Thank you for your inquiry. Here is our response:</p>
                            <p>" . nl2br(htmlspecialchars($response)) . "</p>
                            <p>Best regards,<br>Yana Byahe Na Travel and Tours</p>";
            
            $mail->send();
            
            $_SESSION['message'] = "✅ Response sent successfully";
            header("Location: manage_inquiries.php");
            exit;
        } catch (Exception $e) {
            $message = "❌ Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "❌ Error sending response";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inquiry - Admin Dashboard</title>
    <?php include __DIR__ . "/includes/head_links.php"; ?>
    <!-- Additional CSS for inquiry view page -->
    <style>
        body {
            display: flex;
            background-color: #f5f7fb;
            font-family: 'Poppins', sans-serif;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .sidebar-toggle {
            display: none;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: #3498db;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
        }
        
        .header-left h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: #333;
            font-size: 1.2rem;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
        }
        
        .admin-profile span {
            margin-right: 10px;
            color: #555;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .page-header {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            background-color: #fff;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .card-header h5 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .card-header h5 i {
            margin-right: 10px;
            color: #3498db;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .detail-item {
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-item h6 {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .detail-item p {
            color: #333;
            margin: 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        
        .detail-item p i {
            margin-right: 8px;
            color: #3498db;
        }
        
        .message-content {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #333;
        }
        
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-success {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            border-color: #95a5a6;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
            border-color: #7f8c8d;
        }
        
        .form-select, .form-control {
            border-radius: 6px;
            border: 1px solid #dce4ec;
            padding: 10px 15px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            color: #333;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .alert {
            border-radius: 6px;
            padding: 12px 20px;
            margin-bottom: 20px;
            border: none;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .alert-success {
            background-color: #d5f5e3;
            color: #27ae60;
        }
        
        .card-header.bg-primary {
            background-color: #3498db !important;
            color: white;
        }
        
        .card-header.bg-primary h5 i {
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .action-buttons .btn {
            flex: 1;
            gap: 8px;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu a i {
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
            
            .menu-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-header h3 {
                display: block;
            }
            
            .sidebar-menu a span {
                display: inline;
            }
            
            .sidebar-menu a i {
                margin-right: 10px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .row {
                flex-direction: column;
            }
            
            .col-md-6 {
                width: 100%;
            }
            
            .page-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
        
        /* Status badge colors */
        .badge.bg-new {
            background-color: #3498db;
            color: white;
        }
        
        .badge.bg-in_progress {
            background-color: #f39c12;
            color: white;
        }
        
        .badge.bg-resolved {
            background-color: #2ecc71;
            color: white;
        }
        
        .badge.bg-cancelled {
            background-color: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/includes/sidebar.php"; ?>
    
    <div class="main-content">
        <?php include __DIR__ . "/includes/header.php"; ?>
        
        <header class="page-header">
            <h1><i class="fas fa-envelope-open-text me-2"></i>View Inquiry</h1>
            <a href="manage_inquiries.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Inquiries
            </a>
        </header>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i>Inquiry Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-item">
                            <h6>Name:</h6>
                            <p><?php echo htmlspecialchars($inquiry['name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h6>Email:</h6>
                            <p><i class="fas fa-envelope"></i><?php echo htmlspecialchars($inquiry['email']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h6>Phone:</h6>
                            <p><i class="fas fa-phone"></i><?php echo htmlspecialchars($inquiry['phone'] ?? 'Not provided'); ?></p>
                        </div>
                        <div class="detail-item">
                            <h6>Type:</h6>
                            <p><i class="fas fa-tag"></i><?php echo ucfirst(htmlspecialchars($inquiry['type'] ?? 'General')); ?> Inquiry</p>
                        </div>
                        <div class="detail-item">
                            <h6>Date:</h6>
                            <p><i class="far fa-calendar-alt"></i><?php echo date('F j, Y, g:i a', strtotime($inquiry['created_at'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <h6>Status:</h6>
                            <p>
                                <span class="badge bg-<?php echo $inquiry['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $inquiry['status'])); ?>
                                </span>
                            </p>
                        </div>
                        <?php if ($inquiry['tour_id']): ?>
                        <div class="detail-item">
                            <h6>Package:</h6>
                            <p><i class="fas fa-suitcase"></i><?php echo htmlspecialchars($inquiry['package_name'] ?? 'Unknown Package'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Status Update Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-tasks"></i>Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="new" <?php echo $inquiry['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="in_progress" <?php echo $inquiry['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $inquiry['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-save"></i>Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-comment-alt"></i>Message</h5>
                    </div>
                    <div class="card-body">
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($booking_details): ?>
                <!-- Additional Booking Details for Booking Inquiries -->
                <div class="card mb-4">
                    <div class="card-header bg-primary">
                        <h5><i class="fas fa-calendar-check"></i>Booking Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-item">
                            <h6>Number of Travelers:</h6>
                            <p><i class="fas fa-users"></i><?php echo htmlspecialchars($booking_details['number_of_travelers']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h6>Preferred Date:</h6>
                            <p><i class="far fa-calendar"></i><?php echo date('F j, Y', strtotime($booking_details['preferred_date'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <h6>Budget Range:</h6>
                            <p><i class="fas fa-money-bill-wave"></i><?php echo ucfirst(htmlspecialchars($booking_details['budget_range'])); ?></p>
                        </div>
                        <?php if (!empty($booking_details['special_requests'])): ?>
                        <div class="detail-item">
                            <h6>Special Requests:</h6>
                            <p><i class="fas fa-clipboard-list"></i><?php echo nl2br(htmlspecialchars($booking_details['special_requests'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Response Section -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-reply"></i>Quick Response</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label class="form-label">Email Template</label>
                                <select class="form-select" id="email-template">
                                    <option value="">Select a template</option>
                                    <option value="thank-you">Thank You</option>
                                    <option value="more-info">Request More Information</option>
                                    <option value="booking-confirmation">Booking Confirmation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="response" class="form-label">Response</label>
                                <textarea name="response" id="response-email" class="form-control" rows="6" required></textarea>
                            </div>
                            <input type="hidden" name="status" value="completed">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-primary" onclick="copyToClipboard()">
                                    <i class="fas fa-copy"></i> Copy to Clipboard
                                </button>
                                <!-- <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Send Response
                                </button> -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Email templates
        const templates = {
            'thank-you': `Dear ${<?php echo json_encode($inquiry['name']); ?>},

Thank you for your inquiry about ${<?php echo json_encode($inquiry['package_name'] ?? 'our services'); ?>}. We appreciate your interest inYana Byahe Na Travel and Tours.

We have received your message and will get back to you shortly with more information.

Best regards,
Yana Byahe Na Travel and Tours`,
            'more-info': `Dear ${<?php echo json_encode($inquiry['name']); ?>},

Thank you for your inquiry about ${<?php echo json_encode($inquiry['package_name'] ?? 'our services'); ?>}.

To better assist you, could you please provide the following additional information:
- Your preferred travel dates
- Number of travelers
- Any specific requirements or preferences

Looking forward to hearing from you.

Best regards,
Yana Byahe Na Travel and Tours`,
            'booking-confirmation': `Dear ${<?php echo json_encode($inquiry['name']); ?>},

Thank you for your booking inquiry for ${<?php echo json_encode($inquiry['package_name'] ?? 'our services'); ?>}.

We're pleased to confirm that we have availability for your requested dates. To proceed with your booking, please:
1. Review the attached booking details
2. Complete the payment of the deposit (50% of the total amount)
3. Return the signed booking form

If you have any questions, please don't hesitate to contact us.

Best regards,
Yana Byahe Na Travel and Tours`
        };
        
        // Update email content when template is selected
        document.getElementById('email-template').addEventListener('change', function() {
            const template = this.value;
            if (template && templates[template]) {
                document.getElementById('response-email').value = templates[template];
            }
        });
        
        // Copy email content to clipboard
        function copyToClipboard() {
            const emailContent = document.getElementById('response-email');
            emailContent.select();
            document.execCommand('copy');
            alert('Email content copied to clipboard!');
        }
        
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const menuToggle = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>