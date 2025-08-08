<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";


// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS featured_destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    featured_order INT NOT NULL DEFAULT 1,
    highlight_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
)";
$conn->query($sql);

// Check for existing featured order
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destination_id = $_POST['destination_id'];
    $highlight_text = trim($_POST['highlight_text']);
    $featured_order = (int)$_POST['featured_order'];
    
    // Validate inputs
    if (empty($destination_id) || empty($highlight_text) || $featured_order < 1) {
        $_SESSION['error'] = "❌ All fields are required and display order must be positive";
        header("Location: add_featured.php");
        exit;
    }

    // Check if destination exists and is not already featured
    $check = $conn->prepare("SELECT id, featured FROM destinations WHERE id = ?");
    $check->bind_param("i", $destination_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "❌ Invalid destination selected";
        header("Location: add_featured.php");
        exit;
    }
    
    $dest = $result->fetch_assoc();
    if ($dest['featured'] == 1) {
        $_SESSION['error'] = "❌ This destination is already featured";
        header("Location: add_featured.php");
        exit;
    }

    // Check if featured_order already exists
    $order_check = $conn->prepare("SELECT id FROM featured_destinations WHERE featured_order = ?");
    $order_check->bind_param("i", $featured_order);
    $order_check->execute();
    
    if ($order_check->get_result()->num_rows > 0) {
        // If order exists, shift existing orders up
        $conn->query("UPDATE featured_destinations SET featured_order = featured_order + 1 
                     WHERE featured_order >= $featured_order");
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update destination as featured
        $stmt = $conn->prepare("UPDATE destinations SET featured = 1 WHERE id = ?");
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();

        // Add to featured_destinations table
        $stmt = $conn->prepare("INSERT INTO featured_destinations (destination_id, featured_order, highlight_text) 
                              VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $destination_id, $featured_order, $highlight_text);
        $stmt->execute();

        $conn->commit();
        $_SESSION['message'] = "✅ Destination added to featured successfully";
        header("Location: manage_featured.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "❌ Error adding featured destination: " . $e->getMessage();
        header("Location: add_featured.php");
        exit;
    }
}

// Fetch non-featured destinations for the dropdown
$destinations = $conn->query("
    SELECT * FROM destinations 
    WHERE featured = 0 AND status = 1 
    ORDER BY name ASC
");

// Get current maximum featured order
$max_order = $conn->query("SELECT MAX(featured_order) as max_order FROM featured_destinations")->fetch_assoc();
$next_order = ($max_order['max_order'] ?? 0) + 1;

?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Featured Destination - Admin Dashboard</title>
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
                    <h1>Add Featured Destination</h1>
                    <a href="manage_featured.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </header>

            <div class="card">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="destination_id" class="form-label">Select Destination</label>
                            <select name="destination_id" id="destination_id" class="form-select" required>
                                <option value="">Choose a destination...</option>
                                <?php while($destination = $destinations->fetch_assoc()): ?>
                                    <option value="<?php echo $destination['id']; ?>">
                                        <?php echo htmlspecialchars($destination['name']); ?> - 
                                        <?php echo htmlspecialchars($destination['country']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="featured_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="featured_order" name="featured_order" 
                                   min="1" value="<?php echo $next_order; ?>" required>
                            <small class="text-muted">Current maximum order is <?php echo $max_order['max_order'] ?? 0; ?></small>
                        </div>

                        <div class="mb-3">
                            <label for="highlight_text" class="form-label">Highlight Text</label>
                            <textarea class="form-control" id="highlight_text" name="highlight_text" 
                                      rows="4" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add to Featured
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>