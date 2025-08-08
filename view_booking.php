<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Define company email constant
define('COMPANY_EMAIL', 'yanatours@gmail.com');

$message = "";
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch booking details
$stmt = $conn->prepare("SELECT b.*, u.name as username, u.email, u.phone,
                       p.name as tour_title, p.price as tour_price,
                       b.number_of_travelers, b.booking_date, b.special_requests,
                       b.total_amount, b.status, b.payment_status,
                       b.created_at, b.updated_at
                       FROM bookings b 
                       LEFT JOIN users u ON b.user_id = u.id 
                       LEFT JOIN packages p ON b.package_id = p.id 
                       WHERE b.id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: manage_bookings.php");
    exit;
}

// Handle booking updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_booking'])) {
        $status = $_POST['status'];
        $payment_status = $_POST['payment_status'];
        $special_requests = $_POST['special_requests'];
        
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, payment_status = ?, special_requests = ? WHERE id = ?");
        $stmt->bind_param("sssi", $status, $payment_status, $special_requests, $booking_id);
        
        if ($stmt->execute()) {
            // Send email notification to the actual customer email
            $to = $booking['email']; // This will be the customer's email
            $subject = "Booking Update - #" . $booking_id;
            $headers = "From: " . COMPANY_EMAIL . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $email_body = "<p>Dear " . htmlspecialchars($booking['username']) . ",</p>";
            $email_body .= "<p>Your booking status has been updated:</p>";
            $email_body .= "<ul>";
            $email_body .= "<li>Booking Status: " . ucfirst($status) . "</li>";
            $email_body .= "<li>Payment Status: " . ucfirst($payment_status) . "</li>";
            $email_body .= "</ul>";
            $email_body .= "<p>Best regards,<br>Your Travel Team</p>";
            
            mail($to, $subject, $email_body, $headers);
            
            $_SESSION['message'] = "✅ Booking updated successfully";
            header("Location: manage_bookings.php");
            exit;
        } else {
            $message = "❌ Error updating booking";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/view_booking.css">
</head>
<body>

<div class="admin-container">
    <?php include "includes/sidebar.php"; ?>

    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-content">
                <h1>View Booking #<?php echo $booking_id; ?></h1>
                <a href="manage_bookings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Booking Details</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Customer Name</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($booking['username']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($booking['email']); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tour Package</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($booking['tour_title']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Booking Date</label>
                                    <p class="form-control-static"><?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Number of Persons</label>
                                    <p class="form-control-static"><?php echo $booking['number_of_travelers']; ?></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Price per Person</label>
                                    <p class="form-control-static">₱<?php echo number_format($booking['tour_price'], 2); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total Amount</label>
                                    <p class="form-control-static">₱<?php echo number_format($booking['total_amount'], 2); ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Booking Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="unpaid" <?php echo $booking['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                        <option value="paid" <?php echo $booking['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        <option value="refunded" <?php echo $booking['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Special Requests</label>
                                <textarea name="special_requests" class="form-control" rows="3"><?php echo htmlspecialchars($booking['special_requests']); ?></textarea>
                            </div>

                            <button type="submit" name="update_booking" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Booking
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Booking Timeline</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h4>Booking Created</h4>
                                    <p><?php echo date('F d, Y H:i:s', strtotime($booking['created_at'])); ?></p>
                                </div>
                            </div>
                            <?php if ($booking['status'] != 'pending'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?php echo $booking['status'] == 'confirmed' ? 'success' : 'danger'; ?>"></div>
                                <div class="timeline-content">
                                    <h4>Status Updated to <?php echo ucfirst($booking['status']); ?></h4>
                                    <p><?php echo isset($booking['updated_at']) ? date('F d, Y H:i:s', strtotime($booking['updated_at'])) : date('F d, Y H:i:s'); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($booking['payment_status'] == 'paid'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h4>Payment Received</h4>
                                    <p><?php echo date('F d, Y H:i:s', strtotime($booking['updated_at'])); ?></p>
                                </div>
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
<script src="js/admin.js"></script>
</body>
</html>